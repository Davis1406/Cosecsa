<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillExaminerYears extends Command
{
    protected $signature = 'examiners:backfill-years
                            {--year_id= : Year ID to backfill (default: last completed year)}
                            {--dry-run  : Show what would change without writing}';

    protected $description = 'Add the prior exam year to examiners_history.examination_years for every examiner who participated in that year (from mcs_results, gs_results, and examiner_participations).';

    public function handle(): int
    {
        // ── Resolve the year to backfill ──────────────────────────────────────
        $currentYearId = (int) DB::table('years')
            ->whereExists(function ($q) {
                $q->from('exams_groups')->whereColumn('exams_groups.year_id', 'years.id');
            })
            ->orderByDesc('id')
            ->value('id') ?: (int) DB::table('years')->orderByDesc('id')->value('id');

        $yearId = (int) ($this->option('year_id') ?: ($currentYearId - 1));

        $yearRecord = DB::table('years')->where('id', $yearId)->first();
        if (! $yearRecord) {
            $this->error("No year record found for year_id={$yearId}.");
            return self::FAILURE;
        }
        $yearName = $yearRecord->year_name; // e.g. "2024"

        $dryRun = $this->option('dry-run');
        $this->info(($dryRun ? '[DRY RUN] ' : '') .
            "Backfilling examination year \"{$yearName}\" (year_id={$yearId})...");

        // ── Find examiners who participated in that year ───────────────────────
        $hasParticipations = Schema::hasTable('examiner_participations');

        $participantIds = DB::table('examiners')
            ->where(function ($q) use ($yearId, $hasParticipations) {
                $q->whereExists(function ($sq) use ($yearId) {
                    $sq->from('mcs_results')
                        ->whereColumn('mcs_results.examiner_id', 'examiners.id')
                        ->where('mcs_results.exam_year', $yearId);
                })
                ->orWhereExists(function ($sq) use ($yearId) {
                    $sq->from('gs_results')
                        ->whereColumn('gs_results.examiner_id', 'examiners.id')
                        ->where('gs_results.exam_year', $yearId);
                });

                if ($hasParticipations) {
                    $q->orWhereExists(function ($sq) use ($yearId) {
                        $sq->from('examiner_participations')
                            ->whereColumn('examiner_participations.exm_id', 'examiners.id')
                            ->where('examiner_participations.year_id', $yearId);
                    });
                }
            })
            ->pluck('id');

        $total = $participantIds->count();
        $this->info("Found {$total} examiners who participated in {$yearName}.");

        if ($total === 0) {
            $this->warn('Nothing to do.');
            return self::SUCCESS;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($participantIds as $exmId) {
            $history = DB::table('examiners_history')->where('exm_id', $exmId)->first();

            // ── Decode existing years (handle double-encoding) ─────────────────
            $years = [];
            if ($history && $history->examination_years) {
                $d = json_decode($history->examination_years, true);
                if (is_string($d)) { $d = json_decode($d, true); }
                $years = is_array($d) ? array_map('strval', $d) : [];
            }

            if (in_array((string) $yearName, $years)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $years[] = (string) $yearName;
            sort($years);
            $encoded = json_encode($years); // single-encode; stored correctly

            if (! $dryRun) {
                if ($history) {
                    DB::table('examiners_history')
                        ->where('exm_id', $exmId)
                        ->update([
                            'examination_years' => $encoded,
                            'updated_at'        => now(),
                        ]);
                    $updated++;
                } else {
                    DB::table('examiners_history')->insert([
                        'exm_id'            => $exmId,
                        'examination_years' => $encoded,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                    $created++;
                }
            } else {
                // Dry run — just count
                $history ? $updated++ : $created++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $prefix = $dryRun ? '[DRY RUN] Would have: ' : '';
        $this->info("{$prefix}Created {$created} new history records.");
        $this->info("{$prefix}Updated {$updated} existing history records (year added).");
        $this->info("Skipped  {$skipped} (year already present).");

        if ($dryRun) {
            $this->warn('Nothing was written. Re-run without --dry-run to apply.');
        } else {
            $this->info("Done. Run this command again for the next year when needed.");
        }

        return self::SUCCESS;
    }
}
