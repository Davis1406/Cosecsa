<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DedupTrainees extends Command
{
    protected $signature = 'trainees:dedup {--dry-run : Preview without writing}';
    protected $description = 'Soft-delete duplicate trainee accounts (same PEN, multiple users). Keeps the highest trainee_id; soft-deletes the rest.';

    public function handle(): int
    {
        $dry = $this->option('dry-run');
        if ($dry) {
            $this->warn('[DRY RUN] No changes will be written.');
        }

        // Find all PENs with more than one trainee row
        $dups = DB::table('trainees')
            ->select('entry_number', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('entry_number')
            ->where('entry_number', '!=', '')
            ->groupBy('entry_number')
            ->having('cnt', '>', 1)
            ->orderByDesc('cnt')
            ->get();

        $this->line("PENs with duplicate trainees: {$dups->count()}");

        $softDeleted = 0;
        $skipped     = 0;

        foreach ($dups as $dup) {
            // Fetch all trainee rows for this PEN, ordered descending (keep highest ID)
            $rows = DB::table('trainees as t')
                ->join('users as u', 'u.id', '=', 't.user_id')
                ->where('t.entry_number', $dup->entry_number)
                ->orderByDesc('t.id')
                ->get(['t.id as trainee_id', 't.user_id', 'u.email', 'u.is_deleted', 'u.name']);

            $keepRow   = $rows->first();   // highest trainee_id → keep
            $deleteRows = $rows->slice(1); // all others → soft-delete

            foreach ($deleteRows as $row) {
                if ($row->is_deleted) {
                    // Already soft-deleted — nothing to do
                    $skipped++;
                    continue;
                }

                $this->line(
                    "  {$dup->entry_number}: soft-delete uid={$row->user_id} ({$row->email})"
                    . " → keep tid={$keepRow->trainee_id} ({$keepRow->email})"
                );

                if (!$dry) {
                    DB::table('users')
                        ->where('id', $row->user_id)
                        ->update(['is_deleted' => 1]);
                }
                $softDeleted++;
            }
        }

        $this->newLine();
        $this->info('--- Summary ---');
        if ($dry) {
            $this->warn("Would soft-delete : {$softDeleted} duplicate user(s)");
        } else {
            $this->info("Soft-deleted      : {$softDeleted} duplicate user(s)");
        }
        $this->line("Already deleted   : {$skipped}");
        return self::SUCCESS;
    }
}
