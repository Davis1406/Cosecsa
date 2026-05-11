<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ReconcileFellows extends Command
{
    protected $signature   = 'fellows:reconcile {file : Path to the Excel contacts file}';
    protected $description = 'Keep only fellows from the authoritative Excel list and fix categories';

    // Excel type → category_id
    private array $categoryMap = [
        'fellow by examination'              => 5,
        'fellow by election'                 => 7,
        'foundation fellow'                  => 6,
        'overseas fellow'                    => 9,
        'honorary fellow (asea)'             => 8,
        'honorary fellow (cosecsa)'          => 10,
        'member'                             => 1,
        'member (specialist) by election'    => 2,
        'associate fellow'                   => 4,
    ];

    public function handle(): int
    {
        $file = $this->argument('file');
        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        $this->info("Reading Excel file...");
        $spreadsheet = IOFactory::load($file);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, false);

        // Parse headers from row 1
        $headers = array_map('strtolower', array_map('trim', $rows[0]));
        $nameIdx  = array_search('name', $headers);
        $emailIdx = array_search('email', $headers);
        $typeIdx  = array_search('member/fellow type', $headers);
        $yearIdx  = array_search('member/fellow year', $headers);

        $this->info("Parsing Excel rows...");
        $excelRecords = [];
        for ($i = 1; $i < count($rows); $i++) {
            $row   = $rows[$i];
            $name  = trim($row[$nameIdx] ?? '');
            $email = strtolower(trim($row[$emailIdx] ?? ''));
            $type  = strtolower(trim($row[$typeIdx] ?? ''));
            $year  = $row[$yearIdx] ?? null;

            if (empty($name) && empty($email)) continue;

            // Normalise double spaces in type
            $type = preg_replace('/\s+/', ' ', $type);

            $excelRecords[] = [
                'name'        => $name,
                'email'       => $email,
                'type'        => $type,
                'category_id' => $this->categoryMap[$type] ?? 5,
                'year'        => is_numeric($year) ? (int)$year : null,
            ];
        }

        $this->info("Excel records parsed: " . count($excelRecords));

        // Build lookup sets
        $excelEmails = [];
        $excelNames  = [];
        foreach ($excelRecords as $r) {
            if (!empty($r['email'])) $excelEmails[$r['email']] = $r;
            if (!empty($r['name']))  $excelNames[strtolower($r['name'])] = $r;
        }

        // Get all fellows from DB
        $dbFellows = DB::table('fellows as f')
            ->join('users as u', 'u.id', '=', 'f.user_id')
            ->select('f.id as fellow_id', 'f.user_id', 'u.email', 'u.name',
                     'f.firstname', 'f.lastname')
            ->get();

        $this->info("DB fellows: " . $dbFellows->count());
        $this->newLine();

        // Build additional name lookup: first+last word of excel name → record
        $excelNamePairs = [];
        foreach ($excelRecords as $r) {
            $words = preg_split('/\s+/', strtolower($r['name']));
            $words = array_values(array_filter($words));
            if (count($words) >= 2) {
                // first word + last word
                $key = $words[0] . '|' . end($words);
                $excelNamePairs[$key] = $r;
                // last word + first word (reversed)
                $revKey = end($words) . '|' . $words[0];
                $excelNamePairs[$revKey] = $r;
            }
        }

        $keepFellowIds = [];   // fellow_id → excel record
        $noMatch       = [];   // fellow_ids to delete

        foreach ($dbFellows as $f) {
            $email    = strtolower(trim($f->email ?? ''));
            $fullName = strtolower(trim($f->firstname . ' ' . $f->lastname));
            $dbName   = strtolower(trim($f->name));

            // 1. Match by email (skip placeholder emails)
            if (!empty($email) && strpos($email, '@capsule.import') === false && isset($excelEmails[$email])) {
                $keepFellowIds[$f->fellow_id] = $excelEmails[$email];
                continue;
            }

            // 2. Match by exact full name
            if (isset($excelNames[$fullName])) {
                $keepFellowIds[$f->fellow_id] = $excelNames[$fullName];
                continue;
            }
            if (isset($excelNames[$dbName])) {
                $keepFellowIds[$f->fellow_id] = $excelNames[$dbName];
                continue;
            }

            // 3. Match by first + last word pair (handles middle names & ALL CAPS)
            $fn = strtolower(trim($f->firstname));
            $ln = strtolower(trim($f->lastname));
            if ($fn && $ln) {
                $pairKey = $fn . '|' . $ln;
                $revKey  = $ln . '|' . $fn;
                if (isset($excelNamePairs[$pairKey])) {
                    $keepFellowIds[$f->fellow_id] = $excelNamePairs[$pairKey];
                    continue;
                }
                if (isset($excelNamePairs[$revKey])) {
                    $keepFellowIds[$f->fellow_id] = $excelNamePairs[$revKey];
                    continue;
                }
            }

            // 4. Match by DB full name first+last word pair
            $dbWords = preg_split('/\s+/', $dbName);
            $dbWords = array_values(array_filter($dbWords));
            if (count($dbWords) >= 2) {
                $pairKey = $dbWords[0] . '|' . end($dbWords);
                if (isset($excelNamePairs[$pairKey])) {
                    $keepFellowIds[$f->fellow_id] = $excelNamePairs[$pairKey];
                    continue;
                }
            }

            $noMatch[] = $f->fellow_id;
        }

        $this->info("Matched to Excel: "  . count($keepFellowIds));
        $this->info("Not in Excel (will delete): " . count($noMatch));
        $this->newLine();

        if (!$this->confirm("Proceed? This will DELETE " . count($noMatch) . " fellows not in the Excel list and fix categories for " . count($keepFellowIds) . " fellows.")) {
            $this->warn("Aborted.");
            return 0;
        }

        // ── Step 1: Delete fellows not in Excel ─────────────────────────────
        $this->info("Deleting unmatched fellows...");
        $chunks = array_chunk($noMatch, 200);
        $deletedFellows = 0;
        $deletedUsers   = 0;

        foreach ($chunks as $chunk) {
            // Get user_ids for this chunk
            $userIds = DB::table('fellows')->whereIn('id', $chunk)->pluck('user_id')->toArray();

            // Delete subscriptions & label assignments
            DB::table('fellow_subscriptions')->whereIn('fellow_id', $chunk)->delete();
            DB::table('fellow_label_assignments')->whereIn('fellow_id', $chunk)->delete();

            // Delete fellows
            DB::table('fellows')->whereIn('id', $chunk)->delete();
            $deletedFellows += count($chunk);

            // Delete user_roles and users (only if user has no other roles)
            foreach ($userIds as $uid) {
                $otherRoles = DB::table('user_roles')
                    ->where('user_id', $uid)
                    ->where('role_type', '!=', 7)
                    ->where('is_active', 1)
                    ->count();
                if ($otherRoles === 0) {
                    DB::table('user_roles')->where('user_id', $uid)->delete();
                    DB::table('users')->where('id', $uid)->delete();
                    $deletedUsers++;
                }
            }
        }

        $this->info("Deleted $deletedFellows fellow records and $deletedUsers user accounts.");

        // ── Step 2: Fix category_id and fellowship_year ──────────────────────
        $this->info("Updating categories and fellowship years...");
        $updated = 0;

        foreach ($keepFellowIds as $fellowId => $excelRow) {
            DB::table('fellows')->where('id', $fellowId)->update([
                'category_id'    => $excelRow['category_id'],
                'fellowship_year' => $excelRow['year'] ?? DB::raw('fellowship_year'),
                'updated_at'     => now(),
            ]);
            $updated++;
        }

        $this->info("Updated $updated fellow records.");

        // ── Step 3: Final summary ────────────────────────────────────────────
        $finalCount = DB::table('fellows')->count();

        $this->newLine();
        $this->info("Done!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Excel records',               count($excelRecords)],
                ['Matched & kept',              count($keepFellowIds)],
                ['Deleted (not in Excel)',       $deletedFellows],
                ['Users removed',               $deletedUsers],
                ['Categories & years updated',  $updated],
                ['Final fellows in DB',         $finalCount],
            ]
        );

        return 0;
    }
}
