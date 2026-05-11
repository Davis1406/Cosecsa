<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EnrichFellowsFromCapsule extends Command
{
    protected $signature   = 'fellows:enrich-capsule {token : Capsule CRM API token}';
    protected $description = 'Enrich fellow data (org, specialty, phone, image, intake year, prog entry fee) from Capsule CRM';

    // Map Capsule country names → local country_id
    private array $countryMap = [
        'Angola'                            => 49,
        'Australia'                         => 26,
        'Austria'                           => 58,
        'Bangladesh'                        => 27,
        'Belgium'                           => 28,
        'Botswana'                          => 1,
        'Burundi'                           => 2,
        'Cameroon'                          => 15,
        'Canada'                            => 29,
        'Central Africa Republic'           => 30,
        'Central African Republic'          => 30,
        'Congo, Democratic Republic of the' => 16,
        'Congo - Kinshasa'                  => 16,
        'DRC'                               => 16,
        'Egypt'                             => 54,
        'Eswatini'                          => 23,
        'Swaziland'                         => 23,
        'Ethiopia'                          => 3,
        'Gabon'                             => 17,
        'Gambia'                            => 59,
        'Germany'                           => 31,
        'India'                             => 32,
        'Ireland'                           => 33,
        'Italy'                             => 34,
        'Kenya'                             => 4,
        'Lesotho'                           => 21,
        'Liberia'                           => 35,
        'Madagascar'                        => 19,
        'Malawi'                            => 5,
        'Malaysia'                          => 52,
        'Mozambique'                        => 6,
        'Namibia'                           => 7,
        'Netherlands'                       => 36,
        'New Zealand'                       => 55,
        'Niger'                             => 18,
        'Nigeria'                           => 37,
        'Norway'                            => 38,
        'Palestine'                         => 56,
        'Portugal'                          => 53,
        'Russian Federation'                => 40,
        'Russia'                            => 40,
        'Rwanda'                            => 8,
        'Seychelles'                        => 41,
        'Sierra Leone'                      => 42,
        'Singapore'                         => 57,
        'Somaliland'                        => 20,
        'Somalia'                           => 20,
        'South Africa'                      => 43,
        'South Sudan'                       => 9,
        'Spain'                             => 44,
        'Sudan'                             => 10,
        'Sweden'                            => 46,
        'Switzerland'                       => 45,
        'Tanzania'                          => 11,
        'Tanzania, United Republic of'      => 11,
        'Togo'                              => 22,
        'Uganda'                            => 12,
        'United Arab Emirates'              => 48,
        'United Kingdom'                    => 24,
        'United States'                     => 25,
        'United States of America'          => 25,
        'Zambia'                            => 13,
        'Zimbabwe'                          => 14,
    ];

    public function handle(): int
    {
        $token = $this->argument('token');

        // ── Fetch all Capsule parties tagged "Fellow" ────────────────────────
        $this->info("Fetching all Capsule parties tagged 'Fellow'...");
        $capsuleRecords = $this->fetchAllCapsuleParties($token);

        if (empty($capsuleRecords)) {
            $this->error("No records fetched from Capsule. Check your token.");
            return 1;
        }

        $this->info("Fetched " . count($capsuleRecords) . " Capsule records.");

        // ── Build lookups by email and name ──────────────────────────────────
        $capsuleByEmail    = [];
        $capsuleByName     = [];
        $capsuleByNamePair = [];

        foreach ($capsuleRecords as $rec) {
            $email = strtolower(trim($rec['email'] ?? ''));
            if ($email) {
                $capsuleByEmail[$email] = $rec;
            }

            $first = strtolower(trim($rec['firstName'] ?? ''));
            $last  = strtolower(trim($rec['lastName']  ?? ''));
            $full  = trim("$first $last");
            if ($full) {
                $capsuleByName[$full] = $rec;
            }
            if ($first && $last) {
                $capsuleByNamePair["$first|$last"] = $rec;
                $capsuleByNamePair["$last|$first"] = $rec;
            }
        }

        // ── Get all fellows from DB ──────────────────────────────────────────
        $fellows = DB::table('fellows as f')
            ->join('users as u', 'u.id', '=', 'f.user_id')
            ->select(
                'f.id', 'f.firstname', 'f.lastname', 'f.personal_email',
                'f.organization', 'f.current_specialty', 'f.phone_number',
                'f.address', 'f.country_id', 'f.profile_image',
                'f.admission_year',
                'f.prog_entry_fee_year', 'f.prog_entry_fee_amount_paid',
                'f.prog_entry_mode_payment',
                'u.email as user_email'
            )
            ->get();

        $this->info("DB fellows to process: " . $fellows->count());
        $this->newLine();

        $bar = $this->output->createProgressBar($fellows->count());
        $bar->start();

        $updated      = 0;
        $imgSaved     = 0;
        $intakeSet    = 0;
        $entryFeeSet  = 0;

        foreach ($fellows as $f) {
            $bar->advance();

            $userEmail = strtolower(trim($f->user_email ?? ''));
            $persEmail = strtolower(trim($f->personal_email ?? ''));
            $fn        = strtolower(trim($f->firstname));
            $ln        = strtolower(trim($f->lastname));
            $fullName  = trim("$fn $ln");

            // ── Find matching Capsule record ─────────────────────────────────
            $rec = null;
            if ($userEmail && strpos($userEmail, '@capsule.import') === false && isset($capsuleByEmail[$userEmail])) {
                $rec = $capsuleByEmail[$userEmail];
            } elseif ($persEmail && isset($capsuleByEmail[$persEmail])) {
                $rec = $capsuleByEmail[$persEmail];
            } elseif ($fullName && isset($capsuleByName[$fullName])) {
                $rec = $capsuleByName[$fullName];
            } elseif ($fn && $ln) {
                $pair = "$fn|$ln";
                $rev  = "$ln|$fn";
                if (isset($capsuleByNamePair[$pair]))      $rec = $capsuleByNamePair[$pair];
                elseif (isset($capsuleByNamePair[$rev]))   $rec = $capsuleByNamePair[$rev];
            }

            if (!$rec) continue;

            $update = ['updated_at' => now()];

            // ── Basic fields ─────────────────────────────────────────────────
            if (empty($f->organization) && !empty($rec['organisation'])) {
                $update['organization'] = $rec['organisation'];
            }
            if (empty($f->current_specialty) && !empty($rec['jobTitle'])) {
                $update['current_specialty'] = $rec['jobTitle'];
            }
            if (empty($f->phone_number) && !empty($rec['phone'])) {
                $update['phone_number'] = $rec['phone'];
            }
            if (empty($f->address)) {
                $parts = array_filter([$rec['street'] ?? '', $rec['city'] ?? '']);
                if ($parts) $update['address'] = implode(', ', $parts);
            }
            if (empty($f->country_id) && !empty($rec['country'])) {
                $cid = $this->countryMap[$rec['country']] ?? null;
                if ($cid) $update['country_id'] = $cid;
            }

            // ── Intake year from tags (e.g. "2024 Intake") ───────────────────
            if (empty($f->admission_year) && !empty($rec['tags'])) {
                foreach ($rec['tags'] as $tag) {
                    if (preg_match('/^(\d{4})\s+intake$/i', trim($tag), $m)) {
                        $update['admission_year'] = (int)$m[1];
                        $intakeSet++;
                        break;
                    }
                }
            }

            // ── Programme Entry Fee custom fields ────────────────────────────
            $cf = $rec['customFields'] ?? [];

            if (empty($f->prog_entry_fee_year) && !empty($cf['Programme Entry Fee - Year Paid'])) {
                $yr = $cf['Programme Entry Fee - Year Paid'];
                if (is_numeric($yr)) $update['prog_entry_fee_year'] = (int)$yr;
            }
            if (empty($f->prog_entry_fee_amount_paid) && !empty($cf['Programme Entry Fee - Amount Paid'])) {
                $amt = $cf['Programme Entry Fee - Amount Paid'];
                if (is_numeric($amt)) $update['prog_entry_fee_amount_paid'] = (float)$amt;
            }
            if (empty($f->prog_entry_mode_payment) && !empty($cf['Programme Entry Fee - Payment Verified'])) {
                $update['prog_entry_mode_payment'] = $cf['Programme Entry Fee - Payment Verified'];
            }

            if (isset($update['prog_entry_fee_year']) || isset($update['prog_entry_fee_amount_paid']) || isset($update['prog_entry_mode_payment'])) {
                $entryFeeSet++;
            }

            // ── Profile image ─────────────────────────────────────────────────
            if (empty($f->profile_image) && !empty($rec['pictureURL'])) {
                $picUrl = $rec['pictureURL'];
                if (strpos($picUrl, 'facehub.appspot.com') === false) {
                    try {
                        $imgContents = @file_get_contents($picUrl);
                        if ($imgContents) {
                            $path = 'profile_images/capsule_enrich_' . $rec['id'] . '.jpg';
                            Storage::disk('public')->put($path, $imgContents);
                            $update['profile_image'] = $path;
                            $imgSaved++;
                        }
                    } catch (\Exception $e) {
                        // skip
                    }
                }
            }

            if (count($update) > 1) {
                DB::table('fellows')->where('id', $f->id)->update($update);
                $updated++;
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Fellows enriched',          $updated],
                ['Intake years set',          $intakeSet],
                ['Entry fee records set',     $entryFeeSet],
                ['Profile images saved',      $imgSaved],
            ]
        );

        return 0;
    }

    /**
     * Fetch all Capsule parties tagged "Fellow" with tags & custom fields.
     */
    private function fetchAllCapsuleParties(string $token): array
    {
        $baseUrl = 'https://api.capsulecrm.com/api/v2/parties';
        $records = [];
        $page    = 1;
        $perPage = 100;

        do {
            // embed=tags,customFields fetches tags and custom field values inline
            $url = "$baseUrl?tag=Fellow&perPage=$perPage&page=$page&embed=tags,customFields";
            $ctx = stream_context_create([
                'http' => [
                    'method'  => 'GET',
                    'header'  => "Authorization: Bearer $token\r\nAccept: application/json\r\n",
                    'timeout' => 30,
                ],
            ]);

            $body = @file_get_contents($url, false, $ctx);
            if ($body === false) break;

            $data    = json_decode($body, true);
            $parties = $data['parties'] ?? [];
            if (empty($parties)) break;

            foreach ($parties as $p) {
                // Flatten email, phone, address
                $email = ($p['emailAddresses'][0]['address'] ?? '');
                $phone = ($p['phoneNumbers'][0]['number']   ?? '');
                $addr  = ($p['addresses'][0]                ?? []);

                // Flatten tags → plain string array
                $tags = array_map(fn($t) => $t['name'] ?? $t, $p['tags'] ?? []);

                // Flatten custom fields → key/value map
                $customFields = [];
                foreach ($p['customFields'] ?? [] as $cf) {
                    $key = $cf['definition']['name'] ?? ($cf['label'] ?? '');
                    $val = $cf['value'] ?? $cf['text'] ?? $cf['boolean'] ?? null;
                    if ($key && $val !== null) {
                        $customFields[$key] = $val;
                    }
                }

                $records[] = [
                    'id'           => $p['id'],
                    'firstName'    => $p['firstName'] ?? '',
                    'lastName'     => $p['lastName']  ?? '',
                    'email'        => $email,
                    'phone'        => $phone,
                    'jobTitle'     => $p['jobTitle']  ?? '',
                    'organisation' => $p['organisation']['name'] ?? '',
                    'pictureURL'   => $p['pictureURL'] ?? '',
                    'street'       => $addr['street']  ?? '',
                    'city'         => $addr['city']    ?? '',
                    'country'      => $addr['country'] ?? '',
                    'tags'         => $tags,
                    'customFields' => $customFields,
                ];
            }

            $this->output->write(".");
            $page++;

        } while (count($parties) === $perPage);

        $this->newLine();
        return $records;
    }
}
