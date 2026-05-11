<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportAlumni extends Command
{
    protected $signature = 'alumni:import
                            {file : Path to the alumni Excel (.xlsx) file}
                            {--dry-run : Preview without writing to DB}';

    protected $description = 'Import alumni (FCS Fellows) from the "ALL" sheet of the alumni Excel file.';

    // ── Column indices in the ALL sheet (0-based) ─────────────────────────────
    private const COL_NUM     = 0;
    private const COL_NAME    = 1;
    private const COL_EXAM    = 2;
    private const COL_COUNTRY = 3;
    private const COL_YEAR    = 4;
    private const COL_COMMENT = 5;
    private const COL_EMAIL   = 6;

    // ── Specialty / Exam → programme_id mapping ───────────────────────────────
    private const PROGRAMME_MAP = [
        'fcs general surgery'               => 2,
        'fcs orthopaedic surgery'           => 4,
        'fcs orthopaedics'                  => 4,
        'fcs plastic surgery'               => 8,
        'fcs plastics'                      => 8,
        'fcs urologic surgery'              => 9,
        'fcs urology'                       => 9,
        'fcs paediatric surgery'            => 7,
        'fcs cardiothoracic surgery'        => 1,
        'fcs neurosurgery'                  => 3,
        'fcs otorhinolaryngology'           => 5,
        'fcs paediatric orthopaedic surgery'=> 6,
    ];

    // ── Country name → country_id (matches existing commands) ─────────────────
    private const COUNTRY_MAP = [
        'angola'                                => 49,
        'australia'                             => 26,
        'botswana'                              => 1,
        'burundi'                               => 2,
        'cameroon'                              => 15,
        'central africa republic'               => 30,
        'central african republic'              => 30,
        'democratic republic of the congo'      => 16,
        'drc'                                   => 16,
        'dr congo'                              => 16,
        'congo, the democratic republic of the' => 16,
        'egypt'                                 => 54,
        'ethiopia'                              => 3,
        'eswatini'                              => 23,
        'swaziland'                             => 23,
        'gabon'                                 => 17,
        'gambia'                                => 59,
        'ghana'                                 => null,
        'india'                                 => 32,
        'ireland'                               => 33,
        'kenya'                                 => 4,
        'lesotho'                               => 21,
        'liberia'                               => 35,
        'madagascar'                            => 19,
        'malawi'                                => 5,
        'malaysia'                              => 52,
        'mozambique'                            => 6,
        'namibia'                               => 7,
        'niger'                                 => 18,
        'nigeria'                               => 37,
        'norway'                                => 38,
        'rwanda'                                => 8,
        'seychelles'                            => 41,
        'sierra leone'                          => 42,
        'somalia'                               => 20,
        'somaliland'                            => 20,
        'south africa'                          => 43,
        'south sudan'                           => 9,
        'sudan'                                 => 10,
        'sweden'                                => 46,
        'switzerland'                           => 45,
        'tanzania'                              => 11,
        'tanzania, united republic of'          => 11,
        'united republic of tanzania'           => 11,
        'togo'                                  => 22,
        'uganda'                                => 12,
        'united kingdom'                        => 24,
        'united states'                         => 25,
        'united states of america'              => 25,
        'usa'                                   => 25,
        'zambia'                                => 13,
        'zimbabwe'                              => 14,
        'netherlands'                           => 36,
        'germany'                               => 31,
        'spain'                                 => 44,
        'portugal'                              => 53,
        'canada'                                => 29,
        'singapore'                             => 57,
        'new zealand'                           => 55,
        'united arab emirates'                  => 48,
    ];

    // ── Runtime state ─────────────────────────────────────────────────────────
    private ?int  $fcsCategoryId         = null;
    private bool  $fellowProgrammesExists = false;

    // ── Counters ──────────────────────────────────────────────────────────────
    private int $cntNew        = 0;
    private int $cntUpdated    = 0;
    private int $cntSkipped    = 0;
    private int $cntMultiProg  = 0;
    private int $cntSameProg   = 0;

    public function handle(): int
    {
        ini_set('memory_limit', '512M');

        $file   = $this->argument('file');
        $dryRun = $this->option('dry-run');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        // ── Bootstrap ────────────────────────────────────────────────────────
        $this->resolveFcsCategoryId();
        $this->checkFellowProgrammesTable();

        $this->info('FCS category_id resolved to: ' . ($this->fcsCategoryId ?? 'null (will use 5 as fallback)'));
        $this->info('fellow_programmes table exists: ' . ($this->fellowProgrammesExists ? 'YES' : 'NO'));

        // ── Load spreadsheet ─────────────────────────────────────────────────
        $this->info("Loading spreadsheet: $file");
        $spreadsheet = IOFactory::load($file);

        $sheet = $spreadsheet->getSheetByName('ALL');
        if (!$sheet) {
            $this->error("Sheet \"ALL\" not found. Available sheets: " . implode(', ', $spreadsheet->getSheetNames()));
            return 1;
        }

        // Convert to a plain 0-indexed array of rows; each row is a 0-indexed array of values
        $rawRows = $sheet->toArray(null, true, true, false);

        $totalRows = count($rawRows);
        $this->info("Total rows in sheet (including header): $totalRows");

        if ($dryRun) {
            $this->warn('[DRY RUN] — no data will be written to the database.');
        }

        $this->newLine();
        $bar = $this->output->createProgressBar($totalRows - 1);
        $bar->start();

        foreach ($rawRows as $rowIndex => $row) {
            // Row 0 is the header row — skip it
            if ($rowIndex === 0) {
                continue;
            }

            $bar->advance();

            $rawName = trim((string) ($row[self::COL_NAME] ?? ''));

            // Skip rows with no name
            if ($rawName === '') {
                $this->cntSkipped++;
                continue;
            }

            // ── Parse row values ──────────────────────────────────────────────
            $fullName    = $this->normaliseName($rawName);
            $examRaw     = trim((string) ($row[self::COL_EXAM]    ?? ''));
            $countryRaw  = trim((string) ($row[self::COL_COUNTRY] ?? ''));
            $yearRaw     = trim((string) ($row[self::COL_YEAR]    ?? ''));
            $emailRaw    = strtolower(trim((string) ($row[self::COL_EMAIL]   ?? '')));

            $programmeId  = $this->resolveProgramme($examRaw);
            $countryId    = $this->resolveCountry($countryRaw);
            $fellowYear   = is_numeric($yearRaw) ? (int) $yearRaw : null;
            $email        = filter_var($emailRaw, FILTER_VALIDATE_EMAIL) ? $emailRaw : null;

            // ── Match against existing fellows ────────────────────────────────
            $existingFellow = null;
            $existingUser   = null;

            // Strategy (a): match by email
            if ($email) {
                $existingFellow = $this->findFellowByEmail($email);
            }

            // Strategy (b): match by name if no email match
            if (!$existingFellow) {
                $existingFellow = $this->findFellowByName($fullName);
            }

            if ($existingFellow) {
                // ── EXISTING fellow ───────────────────────────────────────────
                $existingUser = DB::table('users')->find($existingFellow->user_id);
                $this->handleExistingFellow($existingFellow, $existingUser, $programmeId, $fellowYear, $email, $dryRun);
            } else {
                // ── NEW fellow ────────────────────────────────────────────────
                $this->handleNewFellow($fullName, $programmeId, $countryId, $fellowYear, $email, $dryRun);
            }
        }

        $bar->finish();
        $this->newLine(2);

        $mode = $dryRun ? ' [DRY RUN — no data written]' : '';
        $this->info("Done!$mode");
        $this->newLine();

        $this->table(['Metric', 'Count'], [
            ['New fellows created',                        $this->cntNew],
            ['Existing fellows updated/enriched',          $this->cntUpdated],
            ['Rows skipped (no name)',                     $this->cntSkipped],
            ['Multi-programme cases (same person, diff programme)', $this->cntMultiProg],
            ['Existing fellows — same programme (no change needed)', $this->cntSameProg],
        ]);

        return 0;
    }

    // ── Match helpers ─────────────────────────────────────────────────────────

    /**
     * Find a fellow whose personal_email or user email matches the given email.
     */
    private function findFellowByEmail(string $email): ?object
    {
        // Try personal_email on fellows table
        $fellow = DB::table('fellows')
            ->where('personal_email', $email)
            ->first();

        if ($fellow) {
            return $fellow;
        }

        // Try users.email
        $user = DB::table('users')->where('email', $email)->first();
        if ($user) {
            return DB::table('fellows')->where('user_id', $user->id)->first();
        }

        return null;
    }

    /**
     * Find a fellow by normalised name. Matches the first word and last word of
     * the normalised full name against fellows.firstname / fellows.lastname, with
     * a fallback of reversed order. Also checks users.name via a join.
     */
    private function findFellowByName(string $fullName): ?object
    {
        $parts = explode(' ', $fullName);
        if (count($parts) < 2) {
            return null;
        }

        $first = strtolower($parts[0]);
        $last  = strtolower($parts[count($parts) - 1]);

        // fellows.firstname + fellows.lastname
        $fellow = DB::table('fellows')
            ->whereRaw('LOWER(firstname) = ?', [$first])
            ->whereRaw('LOWER(lastname)  = ?', [$last])
            ->first();

        if ($fellow) {
            return $fellow;
        }

        // Try reversed (SURNAME Firstname format stored as firstname=Firstname, lastname=SURNAME)
        $fellow = DB::table('fellows')
            ->whereRaw('LOWER(firstname) = ?', [$last])
            ->whereRaw('LOWER(lastname)  = ?', [$first])
            ->first();

        if ($fellow) {
            return $fellow;
        }

        // Fallback: check users.name via join (handles cases with only users record)
        $result = DB::table('fellows as f')
            ->join('users as u', 'u.id', '=', 'f.user_id')
            ->whereRaw('LOWER(u.name) = ?', [strtolower($fullName)])
            ->select('f.*')
            ->first();

        return $result ?: null;
    }

    // ── Processing ────────────────────────────────────────────────────────────

    private function handleExistingFellow(
        object  $fellow,
        ?object $user,
        ?int    $programmeId,
        ?int    $fellowYear,
        ?string $email,
        bool    $dryRun
    ): void {
        $update = [];

        // Determine programme situation
        $sameProgramme = ($programmeId !== null && $fellow->programme_id == $programmeId);

        if ($programmeId !== null && $fellow->programme_id != $programmeId && $fellow->programme_id !== null) {
            // Multi-programme case: person appears in a different programme
            $this->cntMultiProg++;
            $this->handleMultiProgramme($fellow, $programmeId, $dryRun);
            // Still fall through to update other fields
        } elseif ($sameProgramme) {
            $this->cntSameProg++;
            // No programme change needed, but still enrich other fields below
        } elseif ($programmeId !== null && empty($fellow->programme_id)) {
            // Programme was empty — set it
            $update['programme_id'] = $programmeId;
        }

        // Enrich fellowship_year if empty
        if (empty($fellow->fellowship_year) && $fellowYear !== null) {
            $update['fellowship_year'] = $fellowYear;
        }

        // Enrich personal_email if empty
        if (empty($fellow->personal_email) && $email !== null) {
            $update['personal_email'] = $email;
        }

        // Set category_id to FCS Fellow if it differs or is empty
        $targetCategory = $this->fcsCategoryId ?? 5;
        if (empty($fellow->category_id) || $fellow->category_id != $targetCategory) {
            $update['category_id'] = $targetCategory;
        }

        if ($update) {
            $this->cntUpdated++;
            if (!$dryRun) {
                DB::table('fellows')
                    ->where('id', $fellow->id)
                    ->update($update + ['updated_at' => now()]);
            }
        }
    }

    private function handleMultiProgramme(object $fellow, int $newProgrammeId, bool $dryRun): void
    {
        if ($this->fellowProgrammesExists) {
            // Store the additional programme in fellow_programmes
            $alreadyLogged = DB::table('fellow_programmes')
                ->where('fellow_id', $fellow->id)
                ->where('programme_id', $newProgrammeId)
                ->exists();

            if (!$alreadyLogged && !$dryRun) {
                DB::table('fellow_programmes')->insert([
                    'fellow_id'   => $fellow->id,
                    'programme_id'=> $newProgrammeId,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            $this->line(
                sprintf(
                    '  [MULTI-PROG] fellow_id=%d already has programme_id=%d; additional programme_id=%d stored in fellow_programmes.',
                    $fellow->id,
                    $fellow->programme_id,
                    $newProgrammeId
                )
            );
        } else {
            $this->line(
                sprintf(
                    '  [NEEDS MERGE] fellow_id=%d has programme_id=%d; alumni sheet says programme_id=%d. No fellow_programmes table — manual review needed.',
                    $fellow->id,
                    $fellow->programme_id,
                    $newProgrammeId
                )
            );
        }
    }

    private function handleNewFellow(
        string  $fullName,
        ?int    $programmeId,
        ?int    $countryId,
        ?int    $fellowYear,
        ?string $email,
        bool    $dryRun
    ): void {
        $this->cntNew++;

        if ($dryRun) {
            return;
        }

        // Parse first/last from normalised full name
        $parts     = explode(' ', $fullName);
        $firstName = $parts[0] ?? $fullName;
        $lastName  = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';

        // Generate placeholder email if none provided
        $loginEmail = $email ?? (
            'noemail.alumni.'
            . strtolower(preg_replace('/[^a-z0-9]/i', '', $firstName))
            . '.'
            . strtolower(preg_replace('/[^a-z0-9]/i', '', $lastName))
            . '.'
            . uniqid()
            . '@import'
        );

        $userId = DB::table('users')->insertGetId([
            'name'       => $fullName,
            'email'      => $loginEmail,
            'password'   => Hash::make('alumni'),
            'user_type'  => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('fellows')->insert([
            'user_id'        => $userId,
            'category_id'    => $this->fcsCategoryId ?? 5,
            'firstname'      => $firstName,
            'lastname'       => $lastName,
            'personal_email' => $email,
            'programme_id'   => $programmeId,
            'country_id'     => $countryId,
            'fellowship_year'=> $fellowYear,
            'status'         => 'Active',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    // ── Resolution helpers ────────────────────────────────────────────────────

    /**
     * Look up the "FCS Fellow" (or nearest equivalent) category_id from the DB.
     * Falls back to category_id=5 (Fellow by Examination) if not found.
     */
    private function resolveFcsCategoryId(): void
    {
        // Try common names used in the categories table
        $cat = DB::table('categories')
            ->where(function ($q) {
                $q->whereRaw("LOWER(category_name) LIKE '%fcs%'")
                  ->orWhereRaw("LOWER(category_name) LIKE '%fellow by examination%'")
                  ->orWhereRaw("LOWER(category_name) = 'fellow'");
            })
            ->orderByRaw("CASE
                WHEN LOWER(category_name) LIKE '%fcs%' THEN 0
                WHEN LOWER(category_name) LIKE '%fellow by examination%' THEN 1
                ELSE 2
            END")
            ->first();

        $this->fcsCategoryId = $cat ? (int) $cat->id : 5;
    }

    /**
     * Check whether the fellow_programmes table exists in the database.
     */
    private function checkFellowProgrammesTable(): void
    {
        try {
            $this->fellowProgrammesExists = DB::getSchemaBuilder()->hasTable('fellow_programmes');
        } catch (\Throwable $e) {
            $this->fellowProgrammesExists = false;
        }
    }

    /**
     * Map a specialty/exam string to a programme_id.
     * Returns null for unrecognised values.
     */
    private function resolveProgramme(string $raw): ?int
    {
        if ($raw === '') {
            return null;
        }

        $key = strtolower(preg_replace('/\s+/', ' ', trim($raw)));
        return self::PROGRAMME_MAP[$key] ?? null;
    }

    /**
     * Map a country name to a country_id, first via the local constant map,
     * then with a DB fallback on the countries table (country_name column).
     */
    private function resolveCountry(string $raw): ?int
    {
        if ($raw === '') {
            return null;
        }

        $key = strtolower(trim($raw));

        // Fast path: constant map
        if (array_key_exists($key, self::COUNTRY_MAP)) {
            return self::COUNTRY_MAP[$key];
        }

        // DB fallback
        $row = DB::table('countries')
            ->whereRaw('LOWER(country_name) = ?', [$key])
            ->first();

        return $row ? (int) $row->id : null;
    }

    /**
     * Normalise a name string to Title Case.
     * Handles ALL-CAPS tokens and mixed-case tokens.
     * Input may be "SURNAME Firstname" or "Firstname SURNAME" — we just
     * normalise each token and preserve order (the sheet may vary).
     */
    private function normaliseName(string $raw): string
    {
        $parts = preg_split('/\s+/', trim($raw));
        $parts = array_filter($parts, fn($p) => $p !== '');
        $parts = array_map(fn($p) => mb_convert_case($p, MB_CASE_TITLE, 'UTF-8'), array_values($parts));
        return implode(' ', $parts);
    }
}
