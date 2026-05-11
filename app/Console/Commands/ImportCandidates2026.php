<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class ImportCandidates2026 extends Command
{
    protected $signature   = 'candidates:import {file} {--exam-year=2026} {--dry-run}';
    protected $description = 'Import 2026 exam candidates from Excel spreadsheet';

    // Map spreadsheet programme names → DB programme names
    private array $programmeMap = [
        'mcs'                                => 'MCS',
        'fcs general surgery'                => 'FCS General Surgery',
        'fcs cardiothoracic surgery'         => 'FCS Cardiothoracic Surgery',
        'fcs neurosurgery'                   => 'FCS Neurosurgery',
        'fcs orthopaedic surgery'            => 'FCS Orthopaedic Surgery',
        'fcs otorhinolaryngology'            => 'FCS Otorhinolaryngology',
        'fcs paediatric orthopaedic surgery' => 'FCS Paediatric Orthopaedic Surgery',
        'fcs paediatric surgery'             => 'FCS Paediatric Surgery',
        'fcs plastic surgery'                => 'FCS Plastic Surgery',
        'fcs urologic surgery'               => 'FCS Urologic Surgery',
        'fcs breast surgery'                 => 'FCS Breast Surgery',
        'fcs upper gi surgery'               => 'FCS Upper Gastrointestinal Surgery',
        'fcs upper gastrointestinal surgery' => 'FCS Upper Gastrointestinal Surgery',
    ];

    // Country name normalisations from spreadsheet → DB
    private array $countryMap = [
        'tanzania, united republic of' => 'Tanzania',
        'tanzania'                     => 'Tanzania',
        'democratic republic of congo' => 'DRC',
        'dr congo'                     => 'DRC',
    ];

    private array $programmeCache = [];
    private array $countryCache   = [];
    private array $hospitalCache  = [];

    public function handle(): int
    {
        $file    = $this->argument('file');
        $year    = $this->option('exam-year');
        $dryRun  = $this->option('dry-run');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        $this->info("Loading spreadsheet…");
        $spreadsheet = IOFactory::load($file);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, true);

        // Detect header row (row 1)
        $header = array_map('strtolower', array_map('trim', $rows[1]));
        $colMap = array_flip($header); // header → column letter

        $this->line("Headers: " . implode(', ', array_keys($colMap)));

        $created = $updated = $skipped = 0;

        for ($rowIdx = 2; $rowIdx <= $sheet->getHighestRow(); $rowIdx++) {
            $raw = $rows[$rowIdx];
            // Map by header position
            $r = [];
            foreach ($header as $col => $hdr) {
                $r[$hdr] = trim((string)($raw[$col] ?? ''));
            }

            $firstName  = $r['first name']  ?? $r['firstname']  ?? '';
            $middleName = $r['middle name']  ?? $r['middlename'] ?? '';
            $lastName   = $r['last name']    ?? $r['lastname']   ?? '';
            $email      = strtolower($r['email'] ?? '');

            if (empty($email) && empty($firstName) && empty($lastName)) {
                continue; // blank row
            }

            $fullName = trim("$firstName $middleName $lastName");
            $fullName = preg_replace('/\s+/', ' ', $fullName);

            // ── Lookups ──────────────────────────────────────────────────
            $pen         = $r['pen'] ?? '';
            $orgName     = $r['organisation'] ?? '';
            $examType    = $r['exam type'] ?? '';
            $gender      = ucfirst(strtolower($r['gender'] ?? ''));
            $countryName = $r['country'] ?? '';

            $repeatP1 = !empty($r['repeat pi'])  ? 'Yes' : 'No';
            $repeatP2 = !empty($r['repeat pii']) ? 'Yes' : 'No';
            $mmed     = !empty($r['mmed'])        ? 'Yes' : 'No';
            $sponsor  = $r['sponsor'] ?? '';

            $invoiceNum    = $this->nullify($r['invoice #'] ?? $r['invoice number'] ?? '');
            $invoiceDateRaw= $r['invoice date'] ?? '';
            $invoiceAmount = (int) preg_replace('/[^0-9]/', '', $r['amount'] ?? '0');
            $invoiceSent   = (strtolower($r['invoice sent'] ?? '') === 'sent') ? 'Sent' : 'Pending';
            $feePaid       = !empty($r['fee paid']) && strtolower($r['fee paid']) !== 'no' ? 'Yes' : 'No';
            $payDateRaw    = $r['date'] ?? '';
            $amountPaid    = (int) preg_replace('/[^0-9]/', '', $r['amount paid'] ?? '0');
            $modeOfPayment = $r['mode of payment'] ?? '';
            $remarks       = $r['comments'] ?? $r['remarks'] ?? '';

            $invoiceDate = $this->parseDate($invoiceDateRaw);
            $paymentDate = $this->parseDate($payDateRaw);

            // Resolve IDs
            $programmeId = $this->resolveProgramme($examType);
            $countryId   = $this->resolveCountry($countryName);
            $hospitalId  = $this->resolveHospital($orgName);

            if (!$programmeId) {
                $this->warn("Row $rowIdx: Unknown programme '$examType' for $fullName — skipped");
                $skipped++;
                continue;
            }
            if (!$countryId) {
                $this->warn("Row $rowIdx: Unknown country '$countryName' for $fullName — skipped");
                $skipped++;
                continue;
            }

            // ── Find or create user ───────────────────────────────────────
            $user = null;
            if ($email) {
                $user = DB::table('users')->where('email', $email)->orWhere('email', $email)->first();
                if (!$user) {
                    // Try personal_email in candidates table
                    $cand = DB::table('candidates')->where('personal_email', $email)->first();
                    if ($cand) {
                        $user = DB::table('users')->where('id', $cand->user_id)->first();
                    }
                }
            }

            if (!$user) {
                // Try name match
                $user = DB::table('users')->where('name', $fullName)->first();
            }

            $userId = null;
            if ($user) {
                $userId = $user->id;
            } else {
                if (!$dryRun) {
                    $userId = DB::table('users')->insertGetId([
                        'name'       => $fullName,
                        'email'      => $email ?: strtolower(str_replace(' ', '.', $fullName)) . '@cosecsa.temp',
                        'password'   => Hash::make(str_replace(' ', '', $fullName) . '2026'),
                        'user_type'  => 3,
                        'is_deleted' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    DB::table('user_roles')->insert([
                        'user_id'    => $userId,
                        'role_type'  => 3,
                        'is_active'  => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $this->line("  ✚ Created user: $fullName");
                } else {
                    $this->line("  [DRY] Would create user: $fullName");
                    $userId = 0;
                }
                $created++;
            }

            // ── Check for existing candidate record for this user + year ──
            $existingCand = $userId
                ? DB::table('candidates')->where('user_id', $userId)->where('exam_year', $year)->first()
                : null;

            $candData = [
                'user_id'        => $userId,
                'firstname'      => $firstName,
                'middlename'     => $middleName,
                'lastname'       => $lastName,
                'personal_email' => $email,
                'gender'         => $gender,
                'programme_id'   => $programmeId,
                'hospital_id'    => $hospitalId,
                'country_id'     => $countryId,
                'entry_number'   => $pen ?: null,
                'repeat_paper_one' => $repeatP1,
                'repeat_paper_two' => $repeatP2,
                'mmed'           => $mmed,
                'sponsor'        => $sponsor ?: null,
                'invoice_number' => $invoiceNum,
                'invoice_date'   => $invoiceDate,
                'invoice_amount' => $invoiceAmount ?: null,
                'invoice_status' => $invoiceSent,
                'fee_paid'       => $feePaid,
                'payment_date'   => $paymentDate,
                'amount_paid'    => $amountPaid ?: null,
                'mode_of_payment'=> $modeOfPayment ?: null,
                'remarks'        => $remarks ?: null,
                'exam_year'      => $year,
                'updated_at'     => now(),
            ];

            if (!$existingCand) {
                $candData['created_at'] = now();
                if (!$dryRun) {
                    DB::table('candidates')->insert($candData);
                } else {
                    $this->line("  [DRY] Would insert candidate: $fullName ($examType)");
                }
                $created++;
            } else {
                if (!$dryRun) {
                    DB::table('candidates')->where('id', $existingCand->id)->update($candData);
                } else {
                    $this->line("  [DRY] Would update candidate: $fullName");
                }
                $updated++;
            }
        }

        $this->info("\nDone! Created: $created | Updated: $updated | Skipped: $skipped");
        return 0;
    }

    private function nullify(string $v): ?string
    {
        return (empty($v) || strtolower($v) === 'null') ? null : $v;
    }

    private function parseDate(string $raw): ?string
    {
        $raw = trim($raw);
        if (empty($raw) || strtolower($raw) === 'null') {
            return null;
        }
        $formats = ['d/m/Y', 'D/M/Y', 'm/d/Y', 'Y-m-d', 'd-m-Y', 'd/n/Y', 'n/j/Y'];
        foreach ($formats as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $raw);
                if ($d && $d->year >= 2000 && $d->year <= 2030) {
                    return $d->format('Y-m-d');
                }
            } catch (\Exception $e) {}
        }
        return null;
    }

    private function resolveProgramme(string $name): ?int
    {
        $key = strtolower(trim($name));
        if (isset($this->programmeCache[$key])) {
            return $this->programmeCache[$key];
        }
        // Direct name lookup
        $norm = $this->programmeMap[$key] ?? $name;
        $id   = DB::table('programmes')->whereRaw('LOWER(name) = ?', [strtolower($norm)])->value('id');
        if (!$id) {
            // Partial match
            $id = DB::table('programmes')->whereRaw('LOWER(name) LIKE ?', ['%' . $key . '%'])->value('id');
        }
        $this->programmeCache[$key] = $id;
        return $id;
    }

    private function resolveCountry(string $name): ?int
    {
        $key = strtolower(trim($name));
        if (isset($this->countryCache[$key])) {
            return $this->countryCache[$key];
        }
        $norm = $this->countryMap[$key] ?? $name;
        $id   = DB::table('countries')->whereRaw('LOWER(country_name) = ?', [strtolower($norm)])->value('id');
        if (!$id) {
            $id = DB::table('countries')->whereRaw('LOWER(country_name) LIKE ?', ['%' . strtolower($norm) . '%'])->value('id');
        }
        $this->countryCache[$key] = $id;
        return $id;
    }

    private function resolveHospital(string $name): ?int
    {
        if (empty($name)) return null;
        $key = strtolower(trim($name));
        if (isset($this->hospitalCache[$key])) {
            return $this->hospitalCache[$key];
        }
        // Exact match first
        $id = DB::table('hospitals')->whereRaw('LOWER(name) = ?', [$key])->value('id');
        if (!$id) {
            // Partial match
            $words = explode(' ', $key);
            $sig   = implode('%', array_slice($words, 0, min(3, count($words))));
            $id    = DB::table('hospitals')->whereRaw('LOWER(name) LIKE ?', ["%$sig%"])->value('id');
        }
        $this->hospitalCache[$key] = $id;
        return $id;
    }
}
