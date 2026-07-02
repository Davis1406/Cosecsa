<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportCapsuleContacts extends Command
{
    protected $signature   = 'capsule:import-contacts';
    protected $description = 'Import the Davis Fellows saved list from Capsule CRM into the local capsule_contacts table';

    // Davis Fellows filter (ID 366658) — orGroup of 14 tag IDs discovered via GET /api/v2/parties/filters
    protected array $davisFellowsFilter = [
        'filter' => [
            'conditions' => [
                [
                    'orGroup' => [
                        ['field' => 'tag', 'operator' => 'is', 'value' => 5762179],
                        ['field' => 'tag', 'operator' => 'is', 'value' => 5595146],
                        ['field' => 'tag', 'operator' => 'is', 'value' => 694195],
                        ['field' => 'tag', 'operator' => 'is', 'value' => 4255657],
                        ['field' => 'tag', 'operator' => 'is', 'value' => 2899668],
                        ['field' => 'tag', 'operator' => 'is', 'value' => 3606842],
                        ['field' => 'tag', 'operator' => 'is', 'value' => 3885584],
                        ['field' => 'tag', 'operator' => 'is', 'value' => 813250],
                        ['field' => 'tag', 'operator' => 'is', 'value' => 963618],
                        ['field' => 'tag', 'operator' => 'is', 'value' => 813249],
                        ['field' => 'tag', 'operator' => 'is', 'value' => 3606817],
                        ['field' => 'tag', 'operator' => 'is', 'value' => 1311997],
                        ['field' => 'tag', 'operator' => 'is', 'value' => 1311996],
                        ['field' => 'tag', 'operator' => 'is', 'value' => 1311995],
                    ],
                ],
            ],
        ],
    ];

    public function handle(): int
    {
        $token = config('services.capsule.token');
        if (! $token) {
            $this->error('CAPSULE_API_TOKEN not set.');
            return Command::FAILURE;
        }

        $this->info('Fetching Davis Fellows contacts from Capsule CRM…');

        $page    = 1;
        $total   = 0;
        $hasMore = true;
        $now     = now();

        while ($hasMore) {
            $response = Http::withToken($token)
                ->withHeaders(['Accept' => 'application/json'])
                ->timeout(30)
                ->post('https://api.capsulecrm.com/api/v2/parties/filters/results?perPage=100&page=' . $page . '&embed=tags', $this->davisFellowsFilter);

            if (! $response->successful()) {
                $this->error("API error on page {$page}: " . $response->body());
                break;
            }

            $parties = $response->json('parties', []);
            if (empty($parties)) {
                break;
            }

            foreach ($parties as $party) {
                $email = null;
                foreach ($party['emailAddresses'] ?? [] as $e) {
                    if (! empty($e['address'])) { $email = $e['address']; break; }
                }

                $phone = null;
                foreach ($party['phoneNumbers'] ?? [] as $p) {
                    if (! empty($p['number'])) { $phone = $p['number']; break; }
                }

                $tags = array_column($party['tags'] ?? [], 'name');

                DB::table('capsule_contacts')->upsert([
                    'capsule_id'   => $party['id'],
                    'first_name'   => $party['firstName'] ?? null,
                    'last_name'    => $party['lastName'] ?? null,
                    'email'        => $email,
                    'phone'        => $phone,
                    'organisation' => $party['organisation']['name'] ?? null,
                    'tags'         => implode(', ', $tags),
                    'capsule_url'  => 'https://cosecsatrainees.capsulecrm.com/party/' . $party['id'],
                    'imported_at'  => $now,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ], ['capsule_id'], [
                    'first_name', 'last_name', 'email', 'phone', 'organisation', 'tags', 'capsule_url', 'imported_at', 'updated_at',
                ]);

                $total++;
            }

            $hasMore = $response->header('X-Pagination-Has-More') === 'true';
            $page++;

            $this->info("Page {$page}, imported so far: {$total}");

            // Respect rate limit
            usleep(100000); // 100ms between pages
        }

        $this->info("Done. Total contacts imported: {$total}");
        return Command::SUCCESS;
    }
}
