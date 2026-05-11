<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportExcelFellows extends Command
{
    protected $signature   = 'fellows:import-excel {file : Path to the contacts XLSX file}
                                                    {--dry-run : Preview counts without writing to DB}
                                                    {--subs : Also import Annual Subscriptions into fellow_subscriptions}';
    protected $description = 'Import fellows from the COSECSA contacts Excel file. Handles multi-role users.';

    // ── Category mapping ─────────────────────────────────────────────────────
    private const CATEGORY_MAP = [
        'fellow by examination'             => 5,
        'foundation fellow'                 => 6,
        'fellow by election'                => 7,
        'honorary fellow (asea)'            => 8,
        'overseas fellow'                   => 9,
        'honorary fellow (cosecsa)'         => 10,
        'associate fellow'                  => 7,   // closest match
        'member'                            => 1,
        'member (specialist) by election'   => 2,
        'member specialist'                 => 2,
        'affiliate member'                  => 3,
        'associate member'                  => 4,
    ];

    // ── Country mapping (Excel names → DB country_id) ────────────────────────
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
        'ghana'                                => null,
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

    public function handle(): int
    {
        $file   = $this->argument('file');
        $dryRun = $this->option('dry-run');
        $doSubs = $this->option('subs');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        $this->info("Reading spreadsheet…");
        $spreadsheet = IOFactory::load($file);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, true);  // assoc by col letter

        // Detect header row (row 1) and build column map
        $header    = array_map(fn($h) => trim((string) $h), $rows[1]);
        $colMap    = array_flip($header);   // "Name" => "A", "Email" => "B", etc.

        $total   = count($rows) - 1;  // subtract header
        $this->info("Total data rows: $total");

        // ── Counters ─────────────────────────────────────────────────────────
        $created       = 0;
        $multiRole     = 0;   // existing user, added fellow record
        $enriched      = 0;   // existing fellow, updated fields
        $skipped       = 0;
        $subsImported  = 0;
        $unmatchedCols = [];

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        // Pre-load existing fellows indexed by user_id for fast lookup
        $existingFellowsByUserId = DB::table('fellows')
            ->pluck('id', 'user_id')
            ->toArray();

        for ($rowNum = 2; $rowNum <= count($rows); $rowNum++) {
            $row = $rows[$rowNum] ?? null;
            if (!$row) { $bar->advance(); continue; }

            $bar->advance();

            // ── Extract row values ────────────────────────────────────────────
            $rawName    = trim((string) ($row[$colMap['Name'] ?? ''] ?? ''));
            $email      = strtolower(trim((string) ($row[$colMap['Email'] ?? ''] ?? '')));
            $phone      = trim((string) ($row[$colMap['Phone'] ?? ''] ?? ''));
            $city       = trim((string) ($row[$colMap['Town/City'] ?? ''] ?? ''));
            $country    = trim((string) ($row[$colMap['Country'] ?? ''] ?? ''));
            $typeRaw    = trim((string) ($row[$colMap['Member/Fellow Type'] ?? ''] ?? ''));
            $yearRaw    = $row[$colMap['Member/Fellow Year'] ?? ''] ?? null;
            $subs2024   = trim((string) ($row[$colMap['Annual Subs 2024'] ?? ''] ?? ''));
            $subs2025   = trim((string) ($row[$colMap['Annual Subs 2025'] ?? ''] ?? ''));
            $subs2026   = trim((string) ($row[$colMap['Annual Subs 2026'] ?? ''] ?? ''));

            if (!$rawName) { $skipped++; continue; }

            // ── Parse name ───────────────────────────────────────────────────
            [$firstName, $lastName] = $this->parseName($rawName);

            // ── Resolve category ─────────────────────────────────────────────
            $categoryId = $this->resolveCategory($typeRaw);

            // ── Resolve country ──────────────────────────────────────────────
            $countryId  = $this->resolveCountry($country);

            // ── Fellowship year ──────────────────────────────────────────────
            $fellowYear = is_numeric($yearRaw) ? (int) $yearRaw : null;

            // ── Find or create user ──────────────────────────────────────────
            $existingUser  = null;
            $fellowsRecord = null;

            if ($email) {
                $existingUser = DB::table('users')->where('email', $email)->first();
            }

            // No email or email not found → try name-based match on fellows table
            if (!$existingUser) {
                $fnLower = strtolower($firstName);
                $lnLower = strtolower($lastName);
                $fellowMatch = DB::table('fellows as f')
                    ->join('users as u', 'u.id', '=', 'f.user_id')
                    ->whereRaw('LOWER(f.firstname) = ?', [$fnLower])
                    ->whereRaw('LOWER(f.lastname)  = ?', [$lnLower])
                    ->select('u.id as user_id', 'f.id as fellow_id', 'u.email')
                    ->first();
                if ($fellowMatch) {
                    $existingUser  = (object) ['id' => $fellowMatch->user_id, 'email' => $fellowMatch->email];
                    $fellowsRecord = (object) ['id' => $fellowMatch->fellow_id];
                }
            }

            if ($existingUser) {
                // User exists — check if they already have a fellows record
                $fellowsRecord = DB::table('fellows')
                    ->where('user_id', $existingUser->id)
                    ->first();

                if ($fellowsRecord) {
                    // ── Case 1: Already a fellow → enrich missing fields ──────
                    $update = [];
                    if (empty($fellowsRecord->phone_number) && $phone) $update['phone_number'] = $phone;
                    if (empty($fellowsRecord->country_id)   && $countryId) $update['country_id'] = $countryId;
                    if (empty($fellowsRecord->fellowship_year) && $fellowYear) $update['fellowship_year'] = $fellowYear;
                    if (empty($fellowsRecord->category_id) && $categoryId) $update['category_id'] = $categoryId;
                    if (empty($fellowsRecord->address) && $city) $update['address'] = $city;

                    if ($update && !$dryRun) {
                        DB::table('fellows')->where('id', $fellowsRecord->id)->update($update + ['updated_at' => now()]);
                    }
                    if ($update) $enriched++;
                    // Import subs even for existing fellows
                } else {
                    // ── Case 2: User exists with different role → add fellow ──
                    $multiRole++;
                    if (!$dryRun) {
                        $fellowsRecord = (object) ['id' => DB::table('fellows')->insertGetId([
                            'user_id'        => $existingUser->id,
                            'category_id'    => $categoryId ?? 5,
                            'firstname'      => $firstName,
                            'lastname'       => $lastName,
                            'personal_email' => $email,
                            'phone_number'   => $phone ?: null,
                            'country_id'     => $countryId,
                            'address'        => $city ?: null,
                            'fellowship_year'=> $fellowYear,
                            'status'         => 'Active',
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ])];

                        // Add fellow role (role_type=7) if not already present
                        $hasRole = DB::table('user_roles')
                            ->where('user_id', $existingUser->id)
                            ->where('role_type', 7)
                            ->exists();
                        if (!$hasRole) {
                            DB::table('user_roles')->insert([
                                'user_id'    => $existingUser->id,
                                'role_type'  => 7,
                                'is_active'  => 1,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                        $existingFellowsByUserId[$existingUser->id] = $fellowsRecord->id;
                    }
                }
            } else {
                // ── Case 3: Brand new user ────────────────────────────────────
                $created++;
                if (!$dryRun) {
                    $loginEmail = $email ?: ('noemail.xl.' . strtolower($firstName) . '.' . strtolower($lastName) . '.' . uniqid() . '@excel.import');
                    $userId = DB::table('users')->insertGetId([
                        'name'       => trim("$firstName $lastName"),
                        'email'      => $loginEmail,
                        'password'   => Hash::make(uniqid('', true)),
                        'user_type'  => 7,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    DB::table('user_roles')->insert([
                        'user_id'    => $userId,
                        'role_type'  => 7,
                        'is_active'  => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $fellowId = DB::table('fellows')->insertGetId([
                        'user_id'        => $userId,
                        'category_id'    => $categoryId ?? 5,
                        'firstname'      => $firstName,
                        'lastname'       => $lastName,
                        'personal_email' => $email ?: null,
                        'phone_number'   => $phone ?: null,
                        'country_id'     => $countryId,
                        'address'        => $city ?: null,
                        'fellowship_year'=> $fellowYear,
                        'status'         => 'Active',
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                    $fellowsRecord = (object) ['id' => $fellowId];
                    $existingFellowsByUserId[$userId] = $fellowId;
                }
            }

            // ── Import annual subscriptions ───────────────────────────────────
            if ($doSubs && !$dryRun && $fellowsRecord) {
                $subsMap = [2024 => $subs2024, 2025 => $subs2025, 2026 => $subs2026];
                foreach ($subsMap as $yr => $val) {
                    if (!$val || strtolower($val) === '') continue;
                    $status = $this->mapSubsStatus($val);
                    $exists = DB::table('fellow_subscriptions')
                        ->where('fellow_id', $fellowsRecord->id)
                        ->where('year', $yr)
                        ->exists();
                    if (!$exists) {
                        DB::table('fellow_subscriptions')->insert([
                            'fellow_id'  => $fellowsRecord->id,
                            'year'       => $yr,
                            'status'     => $status,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $subsImported++;
                    }
                }
            }
        }

        $bar->finish();
        $this->newLine(2);

        $mode = $dryRun ? ' [DRY RUN — no data written]' : '';
        $this->info("Done!$mode");
        $this->table(['Metric', 'Count'], [
            ['New fellows created',                       $created],
            ['Multi-role users (fellow record added)',    $multiRole],
            ['Existing fellows enriched',                 $enriched],
            ['Rows skipped (no name)',                    $skipped],
            ['Subscriptions imported',                    $subsImported],
        ]);

        return 0;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function parseName(string $raw): array
    {
        $raw = trim($raw);
        // Normalise ALL-CAPS parts to Title Case
        $parts = explode(' ', $raw);
        $parts = array_map(function ($p) {
            return (strtoupper($p) === $p && strlen($p) > 1) ? ucfirst(strtolower($p)) : $p;
        }, $parts);
        if (count($parts) === 1) {
            return [$parts[0], ''];
        }
        $firstName = array_shift($parts);
        $lastName  = implode(' ', $parts);
        return [$firstName, $lastName];
    }

    private function resolveCategory(string $raw): ?int
    {
        if (!$raw) return null;
        $key = strtolower(preg_replace('/\s+/', ' ', trim($raw)));  // collapse double spaces
        return self::CATEGORY_MAP[$key] ?? null;
    }

    private function resolveCountry(string $raw): ?int
    {
        if (!$raw) return null;
        $key = strtolower(trim($raw));
        return self::COUNTRY_MAP[$key] ?? null;
    }

    private function mapSubsStatus(string $val): string
    {
        $v = strtolower(trim($val));
        if ($v === 'paid')   return 'Paid';
        if ($v === 'nil')    return 'Unpaid';
        if ($v === 'unpaid') return 'Unpaid';
        if ($v === 'waived') return 'Waived';
        return 'Unpaid';
    }
}
