<?php

namespace App\Console\Commands;

use App\Services\CapsuleCrmService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncFellowsToCapsule extends Command
{
    protected $signature   = 'capsule:sync-fellows';
    protected $description = 'Sync all MIS fellows to Capsule CRM (email + name matching)';

    public function handle(CapsuleCrmService $capsule): int
    {
        // Mark any stuck running job as failed first
        DB::table('capsule_sync_log')
            ->where('status', 'running')
            ->update(['status' => 'failed']);

        // Create new log entry
        $logId = DB::table('capsule_sync_log')->insertGetId([
            'status'    => 'running',
            'progress'  => 0,
            'total'     => 0,
            'created'   => 0,
            'updated'   => 0,
            'failed'    => 0,
            'synced_at' => now(),
        ]);

        $fellows = DB::table('fellows')
            ->join('categories', 'fellows.category_id', '=', 'categories.id')
            ->leftJoin('countries', 'fellows.country_id', '=', 'countries.id')
            ->select([
                'fellows.id', 'fellows.firstname', 'fellows.lastname',
                'fellows.personal_email', 'fellows.phone_number',
                'fellows.organization', 'fellows.current_specialty',
                'fellows.address', 'fellows.cosecsa_region',
                'fellows.status', 'fellows.is_promoted',
                'fellows.fellowship_year', 'fellows.candidate_number',
                'fellows.fcs_certificate_number', 'fellows.mcs_certificate_number',
                'categories.category_name', 'countries.country_name',
            ])
            ->get();

        $total   = $fellows->count();
        $created = 0;
        $updated = 0;
        $failed  = 0;
        $done    = 0;

        // Update total now that we know it
        DB::table('capsule_sync_log')->where('id', $logId)->update(['total' => $total]);

        $this->info("Syncing {$total} fellows to Capsule CRM…");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($fellows as $fellow) {
            try {
                $payload = CapsuleCrmService::fellowToPayload($fellow);
                $tags    = CapsuleCrmService::fellowTags($fellow);

                // Match: email first, then full-name fallback
                $existing = null;
                if (! empty($fellow->personal_email)) {
                    $existing = $capsule->findByEmail($fellow->personal_email);
                }
                if (! $existing) {
                    $existing = $capsule->findByName($fellow->firstname, $fellow->lastname);
                }

                if ($existing) {
                    $ok = $capsule->updateContact($existing['id'], $payload);
                    if ($ok && $tags) {
                        $existingTagNames = array_column($existing['tags'] ?? [], 'name');
                        $capsule->setTags($existing['id'], array_unique(array_merge($existingTagNames, $tags)));
                    }
                    $ok ? $updated++ : $failed++;
                } else {
                    $created_party = $capsule->createContact($payload);
                    if ($created_party) {
                        if ($tags) {
                            $capsule->setTags($created_party['id'], $tags);
                        }
                        $created++;
                    } else {
                        $failed++;
                    }
                }

                // Respect ~4 req/s rate limit (250ms per fellow)
                usleep(250000);
            } catch (\Exception $e) {
                Log::error("Capsule sync fellow#{$fellow->id}: " . $e->getMessage());
                $failed++;
            }

            $done++;
            $bar->advance();

            // Update progress every 10 records
            if ($done % 10 === 0) {
                DB::table('capsule_sync_log')->where('id', $logId)->update([
                    'progress' => $done,
                    'created'  => $created,
                    'updated'  => $updated,
                    'failed'   => $failed,
                ]);
            }
        }

        $bar->finish();
        $this->newLine();

        DB::table('capsule_sync_log')->where('id', $logId)->update([
            'status'    => 'completed',
            'progress'  => $done,
            'total'     => $total,
            'created'   => $created,
            'updated'   => $updated,
            'failed'    => $failed,
            'synced_at' => now(),
        ]);

        $this->info("Done: {$created} created, {$updated} updated, {$failed} failed.");

        return Command::SUCCESS;
    }
}
