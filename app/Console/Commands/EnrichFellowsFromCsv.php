<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EnrichFellowsFromCsv extends Command
{
    protected $signature = 'fellows:enrich-csv
                            {file : Path to the contacts CSV export from Capsule}
                            {--dry-run : Preview without writing to DB}
                            {--subs : Also import / update annual subscriptions}
                            {--exams : Also import exam results into fellow_exam_results}';

    protected $description = 'Enrich fellows DB from the full Capsule CSV export (contacts-2026-03-31.csv).';

    // ── Country map (CSV country name → DB country_id) ─────────────────────────
    private const COUNTRY_MAP = [
        'angola'                               => 49,
        'australia'                            => 26,
        'botswana'                             => 1,
        'burundi'                              => 2,
        'cameroon'                             => 15,
        'central africa republic'              => 30,
        'central african republic'             => 30,
        'democratic republic of the congo'     => 16,
        'drc'                                  => 16,
        'dr congo'                             => 16,
        'congo, the democratic republic of the'=> 16,
        'egypt'                                => 54,
        'ethiopia'                             => 3,
        'eswatini'                             => 23,
        'swaziland'                            => 23,
        'gabon'                                => 17,
        'gambia'                               => 59,
        'india'                                => 32,
        'ireland'                              => 33,
        'kenya'                                => 4,
        'lesotho'                              => 21,
        'liberia'                              => 35,
        'madagascar'                           => 19,
        'malawi'                               => 5,
        'malaysia'                             => 52,
        'mozambique'                           => 6,
        'namibia'                              => 7,
        'niger'                                => 18,
        'nigeria'                              => 37,
        'norway'                               => 38,
        'rwanda'                               => 8,
        'seychelles'                           => 41,
        'sierra leone'                         => 42,
        'somalia'                              => 20,
        'somaliland'                           => 20,
        'south africa'                         => 43,
        'south sudan'                          => 9,
        'sudan'                                => 10,
        'sweden'                               => 46,
        'switzerland'                          => 45,
        'tanzania'                             => 11,
        'tanzania, united republic of'         => 11,
        'united republic of tanzania'          => 11,
        'togo'                                 => 22,
        'uganda'                               => 12,
        'united kingdom'                       => 24,
        'united states'                        => 25,
        'united states of america'             => 25,
        'usa'                                  => 25,
        'zambia'                               => 13,
        'zimbabwe'                             => 14,
        'netherlands'                          => 36,
        'germany'                              => 31,
        'spain'                                => 44,
        'portugal'                             => 53,
        'canada'                               => 29,
        'singapore'                            => 57,
        'new zealand'                          => 55,
        'united arab emirates'                 => 48,
    ];

    // ── Subscription column definitions per year ─────────────────────────────────
    // Each entry: [ statusCol, dateCol|null, modeCol|null, amountCol|null ]
    private const SUBS_COLS = [
        2008 => ['Annual Subs 2008',  null,                            null,                                   null],
        2009 => ['Annual Subs 2009',  null,                            null,                                   null],
        2010 => ['Annual Subs 2010',  null,                            null,                                   null],
        2011 => ['Annual Subs 2011',  null,                            null,                                   null],
        2012 => ['Annual Subs 2012',  null,                            null,                                   null],
        2013 => ['Annual Subs 2013',  null,                            null,                                   null],
        2014 => ['Annual Subs 2014',  null,                            null,                                   null],
        2015 => ['Annual Subs 2015',  null,                            null,                                   null],
        2016 => ['Annual Subs 2016',  null,                            null,                                   null],
        2017 => ['Annual Subs 2017',  null,                            null,                                   null],
        2018 => ['Annual Subs 2018',  'Annual Subs 2018 - Date Paid',  'Annual Subs 2018 - Mode of Payment',   'Annual Subs 2018 - Amount Paid'],
        2019 => ['Annual Subs 2019',  'Annual Subs 2019 - Date Paid',  'Annual Subs 2019 - Mode of Payment',   'Annual Subs 2019 - Amount Paid'],
        2020 => ['Annual Subs 2020',  'Annual Subs 2020 - Date Paid',  'Annual Subs 2020 - Mode of Payment',   'Annual Subs 2020 - Amount Paid'],
        2021 => ['Annual Subs 2021',  'Annual Subs 2021 - Date Paid',  'Annual Subs 2021 - Mode of Payment',   'Annual Subs 2021 - Amount'],
        2022 => ['Annual Subs 2022',  'Annual Subs 2022 - Date Paid',  'Annual Subs 2022 - Mode of Payment',   'Annual Subs 2022 - Amount'],
        2023 => ['Annual Sub 2023',   'Annual Sub 2023 - Date Paid',   'Annual Sub 2023 - Mode of Payment',    'Annual Sub 2023 - Amount Paid'],
        2024 => ['Annual Subs 2024',  'Annual Subs 2024 - Date Paid',  'Annual Sub 2024 - Mode of Payment',    'Annual Subs 2024 - Amount Paid'],
        2025 => ['Annual Subs 2025',  'Annual Subs 2025 - Date Paid',  'Annual Subs 2025 - Mode of Payment',   'Annual Subs 2025 - Amount Paid'],
        2026 => ['Annual Subs 2026',  'Annual Subs 2026 - Date Paid',  'Annual Subs 2026 - Mode of Payment',   'Annual Subs 2026 - Amount Paid'],
    ];

    public function handle(): int
    {
        ini_set('memory_limit', '512M');

        $file   = $this->argument('file');
        $dryRun = $this->option('dry-run');
        $doSubs = $this->option('subs');
        $doExams = $this->option('exams');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        // ── Load CSV into memory indexed by Capsule ID, email, and name ──────────
        $this->info("Loading CSV…");
        [$byId, $byEmail, $byName] = $this->loadCsv($file);
        $this->info("CSV rows loaded: " . count($byId) + count(array_diff_key($byEmail, array_flip(array_map(fn($r) => strtolower($r['Email'] ?? ''), $byId)))) . " (indexed by ID: " . count($byId) . ", email: " . count($byEmail) . ", name: " . count($byName) . ")");

        // ── Load all fellows from DB ─────────────────────────────────────────────
        $this->info("Loading fellows from DB…");
        $fellows = DB::table('fellows as f')
            ->join('users as u', 'u.id', '=', 'f.user_id')
            ->select(
                'f.id', 'f.firstname', 'f.lastname', 'f.personal_email',
                'f.gender', 'f.phone_number', 'f.country_id', 'f.organization',
                'f.current_specialty', 'f.candidate_number', 'f.supervised_by',
                'f.registered_by', 'f.secretariat_registration_date',
                'f.prog_entry_fee_year', 'f.prog_entry_fee_amount_paid',
                'f.prog_entry_mode_payment', 'f.prog_entry_fee_verified',
                'f.exam_fee_year', 'f.exam_fee_amount_paid', 'f.exam_fee_mode_payment',
                'f.exam_fee_date_paid', 'f.exam_fee_payment_verified',
                'f.sponsored_by', 'f.mcs_qualification_year', 'f.country_mcs_training',
                'f.exam_year_upcoming', 'f.exam_year_previous', 'f.second_email',
                'f.admission_year', 'f.fellowship_year', 'f.middlename',
                'f.academic_qualifications', 'f.mcs_certificate_number',
                'f.fcs_certificate_number', 'f.registration_date_mcs',
                'f.registration_date_fcs', 'f.specialty_qualification_date',
                'f.cert_name',
                'u.email as user_email'
            )
            ->get();

        $this->info("DB fellows: " . $fellows->count());
        $this->newLine();

        // ── Counters ─────────────────────────────────────────────────────────────
        $matched     = 0;
        $unmatched   = 0;
        $enriched    = 0;
        $subsUpsert  = 0;
        $examInsert  = 0;

        $bar = $this->output->createProgressBar($fellows->count());
        $bar->start();

        foreach ($fellows as $f) {
            $bar->advance();

            $userEmail = strtolower(trim($f->user_email ?? ''));
            $persEmail = strtolower(trim($f->personal_email ?? ''));
            $fn        = strtolower(trim($f->firstname ?? ''));
            $ln        = strtolower(trim($f->lastname  ?? ''));

            // ── Match CSV row ─────────────────────────────────────────────────
            $row = null;

            // 1. Extract Capsule ID from noemail.{id}@capsule.import
            if (preg_match('/^noemail\.(\d+)@capsule\.import$/i', $userEmail, $m)) {
                $row = $byId[(int) $m[1]] ?? null;
            }

            // 2. Real email match
            if (!$row && $userEmail && strpos($userEmail, '@capsule.import') === false && strpos($userEmail, '@excel.import') === false) {
                $row = $byEmail[$userEmail] ?? null;
            }
            if (!$row && $persEmail) {
                $row = $byEmail[$persEmail] ?? null;
            }

            // 3. Name match
            if (!$row && $fn && $ln) {
                $nameKey = "$fn|$ln";
                $row = $byName[$nameKey] ?? $byName["$ln|$fn"] ?? null;
            }

            if (!$row) {
                $unmatched++;
                continue;
            }

            $matched++;

            // ── Build fellows table update ─────────────────────────────────────
            $update = $this->buildFellowUpdate($f, $row);

            if ($update && !$dryRun) {
                DB::table('fellows')->where('id', $f->id)->update($update + ['updated_at' => now()]);
                $enriched++;
            } elseif ($update) {
                $enriched++;
            }

            // ── Annual subscriptions ──────────────────────────────────────────
            if ($doSubs && !$dryRun) {
                $subsUpsert += $this->upsertSubscriptions($f->id, $row);
            }

            // ── Exam results ──────────────────────────────────────────────────
            if ($doExams && !$dryRun) {
                $examInsert += $this->importExamResults($f->id, $row);
            }
        }

        $bar->finish();
        $this->newLine(2);

        $mode = $dryRun ? ' [DRY RUN]' : '';
        $this->info("Done!$mode");
        $this->table(['Metric', 'Count'], [
            ['Fellows matched to CSV row',   $matched],
            ['Fellows unmatched',            $unmatched],
            ['Fellows enriched (fields set)', $enriched],
            ['Subscription rows upserted',   $subsUpsert],
            ['Exam result rows inserted',    $examInsert],
        ]);

        return 0;
    }

    // ── Load CSV ──────────────────────────────────────────────────────────────────
    private function loadCsv(string $file): array
    {
        $byId    = [];
        $byEmail = [];
        $byName  = [];

        $fh = fopen($file, 'r');
        // Handle BOM
        $bom = fread($fh, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($fh);
        }

        $headers = fgetcsv($fh);
        $headers = array_map('trim', $headers);

        while (($cols = fgetcsv($fh)) !== false) {
            if (count($cols) !== count($headers)) continue;
            $row = array_combine($headers, $cols);

            $id = (int) ($row['ID'] ?? 0);
            if ($id) {
                $byId[$id] = $row;
            }

            foreach (['Email', 'Email Address', 'Work Email', 'Home Email'] as $eCol) {
                $em = strtolower(trim($row[$eCol] ?? ''));
                if ($em) {
                    $byEmail[$em] = $row;
                }
            }

            $fn   = strtolower(trim($row['First Name'] ?? ''));
            $ln   = strtolower(trim($row['Last Name']  ?? ''));
            if ($fn && $ln) {
                $byName["$fn|$ln"] = $row;
                $byName["$ln|$fn"] = $row;
            }
        }

        fclose($fh);
        return [$byId, $byEmail, $byName];
    }

    // ── Build update array for fellows table ──────────────────────────────────────
    private function buildFellowUpdate(object $f, array $row): array
    {
        $update = [];

        $this->setIfEmpty($update, $f, 'gender',                     $this->str($row, 'Gender'));
        $this->setIfEmpty($update, $f, 'current_specialty',          $this->str($row, 'Specialty'));
        $this->setIfEmpty($update, $f, 'organization',               $this->str($row, 'Organisation'));
        $this->setIfEmpty($update, $f, 'middlename',                 $this->str($row, 'Middle Name'));
        $this->setIfEmpty($update, $f, 'candidate_number',           $this->str($row, 'Candidate Number'));
        $this->setIfEmpty($update, $f, 'supervised_by',              $this->str($row, 'Supervised by'));
        $this->setIfEmpty($update, $f, 'registered_by',              $this->str($row, 'Registered by'));
        $this->setIfEmpty($update, $f, 'sponsored_by',               $this->str($row, 'Sponsored by'));
        $this->setIfEmpty($update, $f, 'mcs_qualification_year',     $this->str($row, 'MCS Qualification Year'));
        $this->setIfEmpty($update, $f, 'country_mcs_training',       $this->str($row, 'Country of MCS Training'));
        $this->setIfEmpty($update, $f, 'exam_year_upcoming',         $this->str($row, 'Exam Year (upcoming)'));
        $this->setIfEmpty($update, $f, 'exam_year_previous',         $this->str($row, 'Exam Year (previous)'));
        $this->setIfEmpty($update, $f, 'academic_qualifications',    $this->str($row, 'Academic Qualifications'));
        $this->setIfEmpty($update, $f, 'mcs_certificate_number',     $this->str($row, 'MCS Certificate Number'));
        $this->setIfEmpty($update, $f, 'fcs_certificate_number',     $this->str($row, 'FCS Certificate Number'));
        $this->setIfEmpty($update, $f, 'cert_name',                  $this->str($row, 'Names as it should Appear  on your Certificate'));

        // Phone — try multiple columns
        if (empty($f->phone_number)) {
            $phone = $this->str($row, 'Phone Number')
                ?: $this->str($row, 'Mobile Phone')
                ?: $this->str($row, 'Work Phone');
            if ($phone) $update['phone_number'] = $phone;
        }

        // Second email — Home Email or Work Email
        if (empty($f->second_email)) {
            $sec = $this->str($row, 'Home Email') ?: $this->str($row, 'Work Email');
            if ($sec) $update['second_email'] = $sec;
        }

        // Country
        if (empty($f->country_id)) {
            $cid = self::COUNTRY_MAP[strtolower(trim($row['Country'] ?? ''))] ?? null;
            if ($cid) $update['country_id'] = $cid;
        }

        // Dates
        $this->setDateIfEmpty($update, $f, 'secretariat_registration_date', $this->str($row, 'Secretariat Registration date'));
        $this->setDateIfEmpty($update, $f, 'registration_date_mcs',          $this->str($row, 'Registration Date - MCS'));
        $this->setDateIfEmpty($update, $f, 'registration_date_fcs',          $this->str($row, 'Registration Date - FCS'));
        $this->setDateIfEmpty($update, $f, 'specialty_qualification_date',   $this->str($row, 'Specialty Qualification Date'));

        // Programme entry fee
        $this->setIfEmpty($update, $f, 'prog_entry_fee_year',      $this->str($row, 'Programme Entry Fee - Year'));
        $this->setNumericIfEmpty($update, $f, 'prog_entry_fee_amount_paid', $this->str($row, 'Programme Entry Fee - Amount Paid'));
        $this->setIfEmpty($update, $f, 'prog_entry_mode_payment',   $this->str($row, 'Programme Entry - Mode of Payment'));
        if (empty($f->prog_entry_fee_verified)) {
            $v = strtolower(trim($row['Programme Entry Fee - Payment Verified'] ?? ''));
            if ($v === 'true' || $v === '1') $update['prog_entry_fee_verified'] = 1;
        }

        // Examination fee
        $this->setIfEmpty($update, $f, 'exam_fee_year',             $this->str($row, 'Examination Fee - Year'));
        $this->setNumericIfEmpty($update, $f, 'exam_fee_amount_paid', $this->str($row, 'Examination Fee - Amount Paid'));
        $this->setIfEmpty($update, $f, 'exam_fee_mode_payment',      $this->str($row, 'Examination Fee - Mode of Payment'));
        $this->setDateIfEmpty($update, $f, 'exam_fee_date_paid',    $this->str($row, 'Examination Fee - Date Paid'));
        if (empty($f->exam_fee_payment_verified)) {
            $v = strtolower(trim($row['Examination Fee - Payment Verified'] ?? ''));
            if ($v === 'true' || $v === '1') $update['exam_fee_payment_verified'] = 1;
        }

        // Fellowship / admission year
        if (empty($f->fellowship_year) && !empty($row['Member/Fellow Year'])) {
            $update['fellowship_year'] = trim($row['Member/Fellow Year']);
        }
        if (empty($f->admission_year) && !empty($row['Member/Fellow Year'])) {
            $update['admission_year'] = trim($row['Member/Fellow Year']);
        }

        return $update;
    }

    // ── Upsert annual subscriptions ───────────────────────────────────────────────
    private function upsertSubscriptions(int $fellowId, array $row): int
    {
        $count = 0;

        foreach (self::SUBS_COLS as $year => $colDef) {
            [$statusCol, $dateCol, $modeCol, $amountCol] = $colDef;

            $statusVal = trim($row[$statusCol] ?? '');
            if (!$statusVal) continue;

            $status = $this->mapSubsStatus($statusVal);
            $date   = $dateCol   ? $this->parseDate(trim($row[$dateCol]   ?? '')) : null;
            $mode   = $modeCol   ? trim($row[$modeCol]   ?? '') ?: null           : null;
            $amount = $amountCol ? (is_numeric(trim($row[$amountCol] ?? '')) ? (float) $row[$amountCol] : null) : null;

            $existing = DB::table('fellow_subscriptions')
                ->where('fellow_id', $fellowId)
                ->where('year', $year)
                ->first();

            if ($existing) {
                // Update missing detail fields on existing record
                $upd = [];
                if (empty($existing->date_paid)      && $date)   $upd['date_paid']      = $date;
                if (empty($existing->mode_of_payment) && $mode)  $upd['mode_of_payment'] = $mode;
                if (empty($existing->amount_paid)    && $amount !== null) $upd['amount_paid'] = $amount;
                if ($upd) {
                    DB::table('fellow_subscriptions')->where('id', $existing->id)->update($upd + ['updated_at' => now()]);
                    $count++;
                }
            } else {
                DB::table('fellow_subscriptions')->insert([
                    'fellow_id'      => $fellowId,
                    'year'           => $year,
                    'status'         => $status,
                    'date_paid'      => $date,
                    'mode_of_payment'=> $mode,
                    'amount_paid'    => $amount ?? 0,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
                $count++;
            }
        }

        return $count;
    }

    // ── Import exam results ────────────────────────────────────────────────────────
    private function importExamResults(int $fellowId, array $row): int
    {
        $count = 0;

        foreach ([2021, 2022, 2023, 2024] as $year) {
            foreach ([1, 2] as $part) {
                $col = "{$year} Part {$part} Results";
                $raw = trim($row[$col] ?? '');
                if (!$raw) continue;

                // Parse: "71% -PAEDORTH PASS", "55.8% -ORTH PASS", "FAIL", "65.5% -GS PASS"
                $score    = null;
                $examType = null;
                $result   = null;

                if (preg_match('/^([\d.]+)%\s*-\s*(\w+)\s+(PASS|FAIL)/i', $raw, $m)) {
                    $score    = (float) $m[1];
                    $examType = strtoupper($m[2]);
                    $result   = strtoupper($m[3]);
                } elseif (preg_match('/^(PASS|FAIL)/i', $raw, $m)) {
                    $result = strtoupper($m[1]);
                }

                $exists = DB::table('fellow_exam_results')
                    ->where('fellow_id', $fellowId)
                    ->where('year', $year)
                    ->where('part', $part)
                    ->exists();

                if (!$exists) {
                    DB::table('fellow_exam_results')->insert([
                        'fellow_id'  => $fellowId,
                        'year'       => $year,
                        'part'       => $part,
                        'exam_type'  => $examType,
                        'score'      => $score,
                        'result'     => $result,
                        'raw_result' => $raw,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $count++;
                }
            }
        }

        return $count;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────────

    private function str(array $row, string $col): string
    {
        return trim($row[$col] ?? '');
    }

    private function setIfEmpty(array &$update, object $record, string $field, string $value): void
    {
        if ($value !== '' && empty($record->$field)) {
            $update[$field] = $value;
        }
    }

    private function setNumericIfEmpty(array &$update, object $record, string $field, string $value): void
    {
        if ($value !== '' && is_numeric($value) && empty($record->$field)) {
            $update[$field] = (float) $value;
        }
    }

    private function setDateIfEmpty(array &$update, object $record, string $field, string $value): void
    {
        if ($value !== '' && empty($record->$field)) {
            $parsed = $this->parseDate($value);
            if ($parsed) $update[$field] = $parsed;
        }
    }

    private function parseDate(string $raw): ?string
    {
        if (!$raw) return null;
        // Handle M/D/YY or M/D/YYYY formats
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{2,4})$#', $raw, $m)) {
            $y = (int) $m[3];
            if ($y < 100) $y += ($y >= 50 ? 1900 : 2000);
            $mo = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $d  = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            // Validate
            if (checkdate((int)$mo, (int)$d, $y)) {
                return "$y-$mo-$d";
            }
        }
        // Try strtotime as fallback
        $ts = strtotime($raw);
        if ($ts && $ts > 0) {
            return date('Y-m-d', $ts);
        }
        return null;
    }

    private function mapSubsStatus(string $val): string
    {
        $v = strtolower(trim($val));
        if ($v === 'true' || $v === 'paid' || $v === '1') return 'Paid';
        if ($v === 'nil'  || $v === 'unpaid')              return 'Unpaid';
        if ($v === 'waived')                               return 'Waived';
        return 'Paid'; // default when column has any truthy value
    }
}
