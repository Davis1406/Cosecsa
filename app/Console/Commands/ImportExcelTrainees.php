<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportExcelTrainees extends Command
{
    protected $signature   = 'trainees:import-excel
                                {file : Path to the Trainees Master Data XLSX file}
                                {--dry-run : Preview counts without writing to DB}
                                {--sheet=Trainees : Sheet name to read}';

    protected $description = 'Import trainees from the COSECSA Trainees Master Data Excel file.';

    // ── Programme map (Excel Exam Type → programme id) ──────────────────────
    private const PROGRAMME_MAP = [
        'mcs'                                => 10,
        'fcs general surgery'                => 2,
        'fcs cardiothoracic surgery'         => 1,
        'fcs neurosurgery'                   => 3,
        'fcs orthopaedic surgery'            => 4,
        'fcs otorhinolaryngology'            => 5,
        'fcs paediatric orthopaedic surgery' => 6,
        'fcs paediatric surgery'             => 7,
        'fcs plastic surgery'                => 8,
        'fcs urologic surgery'               => 9,
        'fcs breast surgery'                 => 12,
        'fcs upper gastrointestinal surgery' => 13,
    ];

    // Programme duration in years
    private const PROGRAMME_DURATION = [
        10 => 2,   // MCS = 2 years
    ];
    private const DEFAULT_DURATION = 3; // all FCS = 3 years

    // ── Country map (Excel → country_id) ────────────────────────────────────
    private const COUNTRY_MAP = [
        'angola'                        => 49,
        'botswana'                      => 1,
        'burundi'                       => 2,
        'cameroon'                      => 15,
        'congo'                         => 16,
        'drc'                           => 16,
        'democratic republic of the congo' => 16,
        'ethiopia'                      => 3,
        'eswatini'                      => 23,
        'swaziland'                     => 23,
        'gabon'                         => 17,
        'kenya'                         => 4,
        'lesotho'                       => 21,
        'madagascar'                    => 19,
        'malawi'                        => 5,
        'mozambique'                    => 6,
        'namibia'                       => 7,
        'niger'                         => 18,
        'rwanda'                        => 8,
        'south sudan'                   => 9,
        'sudan'                         => 10,
        'tanzania'                      => 11,
        'tanzania, united republic of'  => 11,
        'togo'                          => 22,
        'uganda'                        => 12,
        'zambia'                        => 13,
        'zimbabwe'                      => 14,
        'nigeria'                       => 37,
        'south africa'                  => 43,
    ];

    // ── Cached lookups ───────────────────────────────────────────────────────
    private array $hospitalCache  = [];
    private array $entryCounters  = [];

    public function handle(): int
    {
        ini_set('memory_limit', '512M');

        $file   = $this->argument('file');
        $dryRun = $this->option('dry-run');
        $sheet  = $this->option('sheet');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        // ── Load hospitals once for fuzzy matching ────────────────────────────
        $this->hospitalCache = DB::table('hospitals')
            ->get(['id', 'name', 'country_id'])
            ->keyBy('id')
            ->toArray();

        // ── Load existing trainee entry_numbers for counter ───────────────────
        DB::table('trainees')->pluck('entry_number')->each(function ($en) {
            if (preg_match('/^([A-Z]+\/\d{4})\/(\d+)$/', $en, $m)) {
                $base = $m[1];
                $num  = (int) $m[2];
                if (!isset($this->entryCounters[$base]) || $this->entryCounters[$base] < $num) {
                    $this->entryCounters[$base] = $num;
                }
            }
        });

        $this->info("Reading spreadsheet…");
        $spreadsheet = IOFactory::load($file);

        // Find the requested sheet
        $ws = null;
        foreach ($spreadsheet->getAllSheets() as $s) {
            if (strcasecmp($s->getTitle(), $sheet) === 0) { $ws = $s; break; }
        }
        if (!$ws) {
            $this->error("Sheet '$sheet' not found. Available: " .
                implode(', ', array_map(fn($s) => $s->getTitle(), $spreadsheet->getAllSheets())));
            return 1;
        }

        $rows   = $ws->toArray(null, true, true, true);  // assoc by column letter
        $header = array_map(fn($h) => trim((string) $h), $rows[1]);
        $colMap = array_flip($header);

        $total = count($rows) - 1;
        $this->info("Total data rows: $total");

        // ── Counters ──────────────────────────────────────────────────────────
        $created   = 0;
        $enriched  = 0;
        $multiRole = 0;
        $skipped   = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        for ($rowNum = 2; $rowNum <= count($rows); $rowNum++) {
            $row = $rows[$rowNum] ?? null;
            if (!$row) { $bar->advance(); continue; }
            $bar->advance();

            // ── Extract fields ────────────────────────────────────────────────
            $firstName  = trim((string) ($row[$colMap['First Name']  ?? ''] ?? ''));
            $middleName = trim((string) ($row[$colMap['Middle Name'] ?? ''] ?? ''));
            $lastName   = trim((string) ($row[$colMap['Last Name']   ?? ''] ?? ''));
            $fullName   = trim((string) ($row[$colMap['Name']        ?? ''] ?? ''));
            $email      = strtolower(trim((string) ($row[$colMap['Email']       ?? ''] ?? '')));
            $yearRaw    = $row[$colMap['Year']         ?? ''] ?? null;
            $org        = trim((string) ($row[$colMap['Organisation'] ?? ''] ?? ''));
            $examType   = trim((string) ($row[$colMap['Exam Type']   ?? ''] ?? ''));
            $gender     = trim((string) ($row[$colMap['Gender']      ?? ''] ?? ''));
            $country    = trim((string) ($row[$colMap['Country']     ?? ''] ?? ''));

            // If no split name, derive from full name
            if (!$firstName && !$lastName && $fullName) {
                $parts     = preg_split('/\s+/', $fullName, 2);
                $firstName = $parts[0] ?? '';
                $lastName  = $parts[1] ?? '';
            }

            if (!$firstName && !$lastName) { $skipped++; continue; }

            // Normalise name casing (ALL-CAPS → Title Case)
            $firstName  = $this->normaliseName($firstName);
            $lastName   = $this->normaliseName($lastName);
            $middleName = $this->normaliseName($middleName);

            // ── Resolve lookups ───────────────────────────────────────────────
            $programmeId = self::PROGRAMME_MAP[strtolower($examType)] ?? null;
            $countryId   = self::COUNTRY_MAP[strtolower(trim($country))] ?? null;
            $year        = is_numeric($yearRaw) ? (int) $yearRaw : null;
            $duration    = $programmeId
                ? (self::PROGRAMME_DURATION[$programmeId] ?? self::DEFAULT_DURATION)
                : self::DEFAULT_DURATION;
            $hospitalId  = $this->resolveHospital($org, $countryId, $dryRun);

            // ── Find or create user ───────────────────────────────────────────
            $existingUser    = null;
            $traineeRecord   = null;

            if ($email) {
                $existingUser = DB::table('users')->where('email', $email)->first();
            }

            // Name-based fallback on trainees table
            if (!$existingUser) {
                $match = DB::table('trainees as t')
                    ->join('users as u', 'u.id', '=', 't.user_id')
                    ->whereRaw('LOWER(t.firstname) = ?', [strtolower($firstName)])
                    ->whereRaw('LOWER(t.lastname)  = ?', [strtolower($lastName)])
                    ->select('u.id as user_id', 't.id as trainee_id')
                    ->first();
                if ($match) {
                    $existingUser  = (object) ['id' => $match->user_id];
                    $traineeRecord = (object) ['id' => $match->trainee_id];
                }
            }

            if ($existingUser) {
                $traineeRecord = DB::table('trainees')
                    ->where('user_id', $existingUser->id)
                    ->first();

                if ($traineeRecord) {
                    // ── Enrich existing record ────────────────────────────────
                    $update = [];
                    if (empty($traineeRecord->gender)       && $gender)      $update['gender']       = $gender;
                    if (empty($traineeRecord->country_id)   && $countryId)   $update['country_id']   = $countryId;
                    if (empty($traineeRecord->programme_id) && $programmeId) $update['programme_id'] = $programmeId;
                    if (empty($traineeRecord->hospital_id)  && $hospitalId)  $update['hospital_id']  = $hospitalId;
                    if (empty($traineeRecord->admission_year) && $year)      $update['admission_year']= (string) $year;
                    if (empty($traineeRecord->exam_year)    && $year)        $update['exam_year']    = $year + $duration;
                    if (empty($traineeRecord->middlename)   && $middleName)  $update['middlename']   = $middleName;

                    if ($update && !$dryRun) {
                        DB::table('trainees')->where('id', $traineeRecord->id)
                            ->update($update + ['updated_at' => now()]);
                    }
                    if ($update) $enriched++;

                } else {
                    // ── User exists (other role) → add trainee record ─────────
                    $multiRole++;
                    if (!$dryRun) {
                        $entryNum = $this->generateEntryNumber($programmeId, $year);
                        DB::table('trainees')->insert([
                            'user_id'                    => $existingUser->id,
                            'firstname'                  => $firstName,
                            'middlename'                 => $middleName ?: null,
                            'lastname'                   => $lastName,
                            'personal_email'             => $email ?: ('noemail.tr.' . uniqid() . '@import'),
                            'gender'                     => $gender ?: null,
                            'programme_id'               => $programmeId ?? 10,
                            'hospital_id'                => $hospitalId  ?? $this->fallbackHospital($countryId),
                            'country_id'                 => $countryId   ?? 1,
                            'entry_number'               => $entryNum,
                            'admission_year'             => $year ? (string) $year : null,
                            'exam_year'                  => $year ? $year + $duration : date('Y') + $duration,
                            'programme_period'           => $duration,
                            'training_year'              => null,
                            'status'                     => 'Active',
                            'admission_letter_status'    => 'Pending',
                            'invitation_letter_status'   => 'Pending',
                            'invoice_status'             => 'Pending',
                            'mode_of_payment'            => '',
                            'amount_paid'                => 0,
                            'created_at'                 => now(),
                            'updated_at'                 => now(),
                        ]);
                        DB::table('user_roles')->insertOrIgnore([
                            'user_id' => $existingUser->id, 'role_type' => 2,
                            'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                }
            } else {
                // ── Brand new user ────────────────────────────────────────────
                $created++;
                if (!$dryRun) {
                    $loginEmail = $email ?: ('noemail.tr.' . strtolower($firstName) . '.' . strtolower($lastName) . '.' . uniqid() . '@import');
                    $userId = DB::table('users')->insertGetId([
                        'name'       => trim("$firstName $lastName"),
                        'email'      => $loginEmail,
                        'password'   => Hash::make(uniqid('', true)),
                        'user_type'  => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    DB::table('user_roles')->insert([
                        'user_id'    => $userId, 'role_type'  => 2,
                        'is_active'  => 1,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                    $entryNum = $this->generateEntryNumber($programmeId, $year);
                    DB::table('trainees')->insert([
                        'user_id'                    => $userId,
                        'firstname'                  => $firstName,
                        'middlename'                 => $middleName ?: null,
                        'lastname'                   => $lastName,
                        'personal_email'             => $email ?: $loginEmail,
                        'gender'                     => $gender ?: null,
                        'programme_id'               => $programmeId ?? 10,
                        'hospital_id'                => $hospitalId  ?? $this->fallbackHospital($countryId),
                        'country_id'                 => $countryId   ?? 1,
                        'entry_number'               => $entryNum,
                        'admission_year'             => $year ? (string) $year : null,
                        'exam_year'                  => $year ? $year + $duration : date('Y') + $duration,
                        'programme_period'           => $duration,
                        'training_year'              => null,
                        'status'                     => 'Active',
                        'admission_letter_status'    => 'Pending',
                        'invitation_letter_status'   => 'Pending',
                        'invoice_status'             => 'Pending',
                        'mode_of_payment'            => '',
                        'amount_paid'                => 0,
                        'created_at'                 => now(),
                        'updated_at'                 => now(),
                    ]);
                }
            }
        }

        $bar->finish();
        $this->newLine(2);

        $mode = $dryRun ? ' [DRY RUN]' : '';
        $this->info("Done!$mode");
        $this->table(['Metric', 'Count'], [
            ['New trainees created',                    $created],
            ['Multi-role (trainee record added)',       $multiRole],
            ['Existing trainees enriched',              $enriched],
            ['Rows skipped (no name)',                  $skipped],
        ]);
        $this->info("Total trainees now: " . DB::table('trainees')->count());

        return 0;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function normaliseName(string $s): string
    {
        if (!$s) return $s;
        // If entirely uppercase and > 1 char, convert to title case
        $words = explode(' ', trim($s));
        return implode(' ', array_map(function ($w) {
            return (strtoupper($w) === $w && strlen($w) > 1) ? ucfirst(strtolower($w)) : $w;
        }, $words));
    }

    private function generateEntryNumber(?int $programmeId, ?int $year): string
    {
        // Format: PROG/YEAR/SEQ  e.g. MCS/2024/001, FCSGS/2024/001
        $prefixMap = [
            1 => 'FCSC', 2 => 'FCSGS', 3 => 'FCSN', 4 => 'FCSO',
            5 => 'FCSENT', 6 => 'FCSPAO', 7 => 'FCSP', 8 => 'FCSPLS',
            9 => 'FCSU', 10 => 'MCS', 12 => 'FCSB', 13 => 'FCSUG',
        ];
        $prefix = $prefixMap[$programmeId ?? 10] ?? 'MCS';
        $yr     = $year ?? (int) date('Y');
        $base   = "$prefix/$yr";
        $next   = ($this->entryCounters[$base] ?? 0) + 1;
        $this->entryCounters[$base] = $next;
        return "$base/" . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    private function resolveHospital(string $orgName, ?int $countryId, bool $dryRun): ?int
    {
        if (!$orgName) return null;

        $orgLower = strtolower($orgName);

        // 1. Exact match (case-insensitive)
        foreach ($this->hospitalCache as $h) {
            if (strtolower($h->name) === $orgLower) return $h->id;
        }

        // 2. Partial match (DB name contained in org or vice-versa)
        foreach ($this->hospitalCache as $h) {
            $hLower = strtolower($h->name);
            if (str_contains($orgLower, $hLower) || str_contains($hLower, $orgLower)) {
                return $h->id;
            }
        }

        // 3. Not found — create it
        if (!$dryRun) {
            $newId = DB::table('hospitals')->insertGetId([
                'name'          => $orgName,
                'country_id'    => $countryId ?? 1,
                'hospital_type' => 1,
                'status'        => 0,
                'is_deleted'    => 0,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
            $this->hospitalCache[$newId] = (object) ['id' => $newId, 'name' => $orgName, 'country_id' => $countryId ?? 1];
            $this->line("  + Created hospital: $orgName (id=$newId)");
            return $newId;
        }

        return null;
    }

    private function fallbackHospital(?int $countryId): int
    {
        if ($countryId) {
            // Use first hospital in that country
            foreach ($this->hospitalCache as $h) {
                if ($h->country_id == $countryId) return $h->id;
            }
        }
        return array_key_first($this->hospitalCache) ?? 1;
    }
}
