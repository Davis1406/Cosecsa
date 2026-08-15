<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

// One-off/re-runnable correction pass over the `fellows` table from a
// college-supplied "Data Correction" spreadsheet — mainly used to fix
// fellows recorded under the wrong Fellowship specialty (e.g. moved from
// General Surgery to Urologic Surgery), but also corrects Fellowship Type
// (category_id) and Fellowship Year when the sheet gives a real value.
//
//   php artisan fellows:correct-from-xlsx "/path/to/Data Correction- Fellows.xlsx" --dry-run
//   php artisan fellows:correct-from-xlsx "/path/to/Data Correction- Fellows.xlsx"
//
// Source columns (row 1 headers): Name | Email | Country | Specialty |
// Fellowship Type | Fellowship Year. A cell value of "-" means "no
// correction for this field" — it is skipped, never written as blank.
//
// Matching: email first (only skips the many `noemail.*@capsule.import`
// placeholders and blank/"-" cells), then falls back to a name match built
// from a *sorted set of lowercased words* — order-independent, so it
// doesn't matter whether the sheet's single "Name" column happens to split
// into firstname/lastname the same way the DB does. A name match is only
// used when it resolves to exactly one fellow; 0 or 2+ candidates are
// logged as unmatched/ambiguous rather than guessed at.
class CorrectFellowsFromXlsx extends Command
{
    protected $signature = 'fellows:correct-from-xlsx
                            {file : Path to the Data Correction spreadsheet}
                            {--dry-run : Preview matches/changes without writing to DB}
                            {--log= : Optional path to write a CSV of every field changed}';

    protected $description = 'Correct fellows\' specialty/programme, fellowship type, and fellowship year from a college-supplied correction spreadsheet.';

    // Free-text "Specialty" cell -> the real COSECSA Fellowship programme.
    // Keyed by normalized (lowercased, whitespace-collapsed) cell text for
    // the common exact spellings, falling back to a keyword scan for the
    // messier variants (a handful of rows only).
    private const SPECIALTY_EXACT = [
        'cardiothoracic surgery' => 'cardiothoracic',
        'cardiothoracic/cardiovascular' => 'cardiothoracic',
        'general surgery' => 'general surgery',
        'general' => 'general surgery',
        'neurosurgery' => 'neurosurgery',
        'orthopaedic surgery' => 'orthopaedic surgery',
        'orthopaedics' => 'orthopaedic surgery',
        'paediatric orthopaedic surgery' => 'paediatric orthopaedic',
        'paediatric surgery' => 'paediatric surgery',
        'otorhinolaryngology (ent)' => 'otorhinolaryngology',
        'otorhinolaryngology' => 'otorhinolaryngology',
        'plastic surgery' => 'plastic surgery',
        'urologic surgery' => 'urologic surgery',
        'urology' => 'urologic surgery',
        'breast surgery' => 'breast surgery',
        'upper gastrointestinal surgery' => 'upper gastrointestinal',
    ];

    // Fellowship Type -> categories.id (see `categories` table; "Fellowship"
    // category type). Matches Api/ImportExcelFellows' CATEGORY_MAP.
    private const CATEGORY_MAP = [
        'fellow by examination'     => 5,
        'foundation fellow'         => 6,
        'fellow by election'        => 7,
        'honorary fellow (asea)'    => 8,
        'honorary fellow'           => 10, // sheet just says "Honorary Fellow" / "Hononary Fellow" (typo), no ASEA/COSECSA distinction
        'overseas fellow'           => 9,
    ];

    public function handle(): int
    {
        $path = $this->argument('file');
        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return Command::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $logPath = $this->option('log');

        $this->info('Reading spreadsheet…');
        $spreadsheet = IOFactory::load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        $header = array_map(fn ($h) => trim((string) $h), array_shift($rows));
        $this->info('Header: ' . implode(' | ', $header));

        $programmesByKeyword = DB::table('programmes')
            ->where('programme_type', 'Fellowship')
            ->where('is_deleted', 0)
            ->pluck('name', 'id')
            ->mapWithKeys(fn ($name, $id) => [strtolower(preg_replace('/^FCS\s+/', '', $name)) => $id]);

        $this->info('Loading fellows from DB…');
        $fellows = DB::table('fellows as f')
            ->join('users as u', 'u.id', '=', 'f.user_id')
            ->select('f.id', 'f.firstname', 'f.lastname', 'f.personal_email', 'f.programme_id',
                'f.current_specialty', 'f.category_id', 'f.fellowship_year', 'u.email')
            ->get();
        $this->info("DB fellows: {$fellows->count()}");

        // ── Build lookup indexes ──
        $byEmail = [];
        $byNameKey = []; // nameKey => [fellow, fellow, ...]
        foreach ($fellows as $f) {
            foreach ([$f->email, $f->personal_email] as $email) {
                $email = strtolower(trim((string) $email));
                if ($email && ! str_ends_with($email, '@capsule.import') && ! str_ends_with($email, '@excel.import')) {
                    $byEmail[$email] = $f;
                }
            }
            $key = $this->nameKey(trim(($f->firstname ?? '') . ' ' . ($f->lastname ?? '')));
            if ($key) $byNameKey[$key][] = $f;
        }

        $matched = 0;
        $unmatched = [];
        $ambiguous = [];
        $changed = 0;
        $noopSkipped = 0;
        $changeLog = [];

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        foreach ($rows as $row) {
            $bar->advance();

            $name = $this->clean($row[0] ?? null);
            if (! $name) continue;

            $email = $this->clean($row[1] ?? null);
            $specialtyRaw = $this->clean($row[3] ?? null);
            $fellowshipType = $this->clean($row[4] ?? null);
            $fellowshipYear = $this->clean($row[5] ?? null);

            // ── Match ──
            $fellow = null;
            if ($email) {
                $fellow = $byEmail[strtolower($email)] ?? null;
            }
            if (! $fellow) {
                $candidates = $byNameKey[$this->nameKey($name)] ?? [];
                if (count($candidates) === 1) {
                    $fellow = $candidates[0];
                } elseif (count($candidates) > 1) {
                    $ambiguous[] = $name;
                    continue;
                }
            }
            if (! $fellow) {
                $unmatched[] = $name;
                continue;
            }
            $matched++;

            // ── Build corrections ──
            $update = [];

            if ($specialtyRaw) {
                $normalized = strtolower(preg_replace('/\s+/', ' ', $specialtyRaw));
                $programmeId = null;
                if (isset(self::SPECIALTY_EXACT[$normalized])) {
                    $needle = self::SPECIALTY_EXACT[$normalized];
                    $programmeId = $programmesByKeyword->first(
                        fn ($id, $key) => str_contains($key, $needle)
                    );
                }

                if ($specialtyRaw !== $fellow->current_specialty) {
                    $update['current_specialty'] = $specialtyRaw;
                    $changeLog[] = [$fellow->id, $name, 'current_specialty', $fellow->current_specialty, $specialtyRaw];
                }
                if ($programmeId && $programmeId != $fellow->programme_id) {
                    $update['programme_id'] = $programmeId;
                    $changeLog[] = [$fellow->id, $name, 'programme_id', $fellow->programme_id, $programmeId];
                } elseif (! $programmeId) {
                    // Specialty text given but doesn't match one of the core
                    // COSECSA Fellowship programmes (e.g. "Radiology", "Oral
                    // and Maxillofacial") — update the text label only, leave
                    // any existing programme_id alone rather than guess/null it.
                    $unmatched[] = "{$name} (unmapped specialty: {$specialtyRaw})";
                }
            }

            if ($fellowshipType) {
                $catKey = strtolower(str_replace('hononary', 'honorary', $fellowshipType)); // one known sheet typo
                $categoryId = self::CATEGORY_MAP[$catKey] ?? null;
                if ($categoryId && $categoryId != $fellow->category_id) {
                    $update['category_id'] = $categoryId;
                    $changeLog[] = [$fellow->id, $name, 'category_id', $fellow->category_id, $categoryId];
                }
            }

            if ($fellowshipYear && is_numeric($fellowshipYear) && (int) $fellowshipYear != (int) $fellow->fellowship_year) {
                $update['fellowship_year'] = (int) $fellowshipYear;
                $changeLog[] = [$fellow->id, $name, 'fellowship_year', $fellow->fellowship_year, (int) $fellowshipYear];
            }

            if (! $update) {
                $noopSkipped++;
                continue;
            }

            $changed++;
            if (! $dryRun) {
                DB::table('fellows')->where('id', $fellow->id)->update($update + ['updated_at' => now()]);
            }
        }

        $bar->finish();
        $this->newLine(2);

        $mode = $dryRun ? ' [DRY RUN — no changes written]' : '';
        $this->info("Done!{$mode}");
        $this->table(['Metric', 'Count'], [
            ['Rows matched to a fellow', $matched],
            ['Fellows with a real change', $changed],
            ['Matched but already correct (no-op)', $noopSkipped],
            ['Unmatched / unmapped-specialty rows', count($unmatched)],
            ['Ambiguous name matches (skipped)', count($ambiguous)],
        ]);

        if ($unmatched) {
            $this->warn('Unmatched / unmapped-specialty rows:');
            foreach ($unmatched as $u) $this->line("  - {$u}");
        }
        if ($ambiguous) {
            $this->warn('Ambiguous name matches (more than one fellow with this name — skipped, needs manual review):');
            foreach ($ambiguous as $a) $this->line("  - {$a}");
        }

        if ($logPath) {
            $out = fopen($logPath, 'w');
            fputcsv($out, ['fellow_id', 'name', 'field', 'old_value', 'new_value']);
            foreach ($changeLog as $row) fputcsv($out, $row);
            fclose($out);
            $this->info("Change log written to {$logPath} (" . count($changeLog) . ' field changes).');
        }

        return Command::SUCCESS;
    }

    private function clean($value): ?string
    {
        if ($value === null) return null;
        $value = trim((string) $value);

        return ($value === '' || $value === '-') ? null : $value;
    }

    // Order-independent name key: lowercase, split on whitespace, sort,
    // rejoin — so it doesn't matter which word the sheet vs. the DB treats
    // as "first" vs "last" name, as long as the same words are present.
    private function nameKey(string $name): string
    {
        $words = array_filter(preg_split('/\s+/', strtolower(trim($name))));
        sort($words);

        return implode(' ', $words);
    }
}
