<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RenumberProgressReportTasks extends Command
{
    protected $signature = 'progress-reports:renumber {--dry-run : Show what would change without writing}';

    protected $description = 'Compact every section\'s progress-report "No." values to 1..N, fixing skipped numbers left by deleted rows.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        if ($dry) {
            $this->warn('[DRY RUN] No changes will be written.');
        }

        $participantIds = DB::table('progress_report_tasks')
            ->select('participant_id')
            ->distinct()
            ->pluck('participant_id');

        $changedRows = 0;
        $changedSections = 0;

        foreach ($participantIds as $participantId) {
            $tasks = DB::table('progress_report_tasks')
                ->where('participant_id', $participantId)
                ->orderBy('row_no')
                ->orderBy('id')
                ->get(['id', 'row_no']);

            $position = 0;
            $sectionChanged = false;

            foreach ($tasks as $task) {
                $position++;

                if ((int) $task->row_no === $position) {
                    continue;
                }

                $sectionChanged = true;

                if (! $dry) {
                    DB::table('progress_report_tasks')
                        ->where('id', $task->id)
                        ->update(['row_no' => $position]);
                }
            }

            if ($sectionChanged) {
                $changedSections++;
                $changedRows += $tasks->count();
            }
        }

        $this->info(sprintf(
            '%s sections needed renumbering (%d rows in total).',
            $changedSections,
            $changedRows
        ));

        if (! $dry && $changedSections > 0) {
            $this->info('Done. Reload the report page — the "No." column is now 1..N.');
        }

        return self::SUCCESS;
    }
}
