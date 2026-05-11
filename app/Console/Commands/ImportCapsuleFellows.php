<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserRole;
use App\Models\FellowsModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportCapsuleFellows extends Command
{
    protected $signature   = 'capsule:import-fellows {file : Path to capsule_fellows.json}';
    protected $description = 'Import fellows from Capsule CRM JSON export';

    // Map Capsule country names → local country_id
    private array $countryMap = [
        'Angola'                                    => 49,
        'Australia'                                 => 26,
        'Austria'                                   => 58,
        'Bangladesh'                                => 27,
        'Belgium'                                   => 28,
        'Botswana'                                  => 1,
        'Burundi'                                   => 2,
        'Cameroon'                                  => 15,
        'Canada'                                    => 29,
        'Central Africa Republic'                   => 30,
        'Central African Republic'                  => 30,
        'Congo, Democratic Republic of the'         => 16,
        'Congo - Kinshasa'                          => 16,
        'DRC'                                       => 16,
        'Egypt'                                     => 54,
        'Eswatini'                                  => 23,
        'Swaziland'                                 => 23,
        'Ethiopia'                                  => 3,
        'Gabon'                                     => 17,
        'Gambia'                                    => 59,
        'Germany'                                   => 31,
        'India'                                     => 32,
        'Ireland'                                   => 33,
        'Italy'                                     => 34,
        'Kenya'                                     => 4,
        'Lesotho'                                   => 21,
        'Liberia'                                   => 35,
        'Madagascar'                                => 19,
        'Malawi'                                    => 5,
        'Malaysia'                                  => 52,
        'Mozambique'                                => 6,
        'Namibia'                                   => 7,
        'Netherlands'                               => 36,
        'New Zealand'                               => 55,
        'Niger'                                     => 18,
        'Nigeria'                                   => 37,
        'Norway'                                    => 38,
        'Palestine'                                 => 56,
        'Portugal'                                  => 53,
        'Russian Federation'                        => 40,
        'Russia'                                    => 40,
        'Rwanda'                                    => 8,
        'Seychelles'                                => 41,
        'Sierra Leone'                              => 42,
        'Singapore'                                 => 57,
        'Somaliland'                                => 20,
        'Somalia'                                   => 20,
        'South Africa'                              => 43,
        'South Sudan'                               => 9,
        'Spain'                                     => 44,
        'Sudan'                                     => 10,
        'Sweden'                                    => 46,
        'Switzerland'                               => 45,
        'Tanzania'                                  => 11,
        'Tanzania, United Republic of'              => 11,
        'Togo'                                      => 22,
        'Uganda'                                    => 12,
        'United Arab Emirates'                      => 48,
        'United Kingdom'                            => 24,
        'United States'                             => 25,
        'United States of America'                  => 25,
        'Zambia'                                    => 13,
        'Zimbabwe'                                  => 14,
    ];

    public function handle(): int
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        $records = json_decode(file_get_contents($file), true);
        if (!$records) {
            $this->error("Invalid JSON file.");
            return 1;
        }

        $total     = count($records);
        $imported  = 0;
        $skipped   = 0;
        $noEmail   = 0;
        $imgSaved  = 0;

        $this->info("Processing $total records...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($records as $row) {
            $bar->advance();

            $firstName = trim($row['firstName'] ?? '');
            $lastName  = trim($row['lastName']  ?? '');
            $email     = trim($row['email1']    ?? '');

            // Skip if no name
            if (empty($firstName) && empty($lastName)) {
                $skipped++;
                continue;
            }

            // Generate placeholder email if missing
            if (empty($email)) {
                $slug  = strtolower(preg_replace('/[^a-z0-9]/i', '.', "$firstName.$lastName"));
                $email = "noemail.{$row['id']}@capsule.import";
                $noEmail++;
            }

            // Skip duplicates by email
            if (User::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            // Download profile image if it's a real one (not a placeholder)
            $profileImagePath = null;
            $picUrl = $row['pictureURL'] ?? '';
            if (!empty($picUrl) && strpos($picUrl, 'facehub.appspot.com') === false) {
                try {
                    $imgContents = @file_get_contents($picUrl);
                    if ($imgContents) {
                        $ext  = 'jpg';
                        $path = 'profile_images/capsule_' . $row['id'] . '.' . $ext;
                        Storage::disk('public')->put($path, $imgContents);
                        $profileImagePath = $path;
                        $imgSaved++;
                    }
                } catch (\Exception $e) {
                    // Skip image on error
                }
            }

            // Map country
            $countryName = trim($row['country'] ?? '');
            $countryId   = $this->countryMap[$countryName] ?? null;

            // Build address string
            $addressParts = array_filter([$row['street'] ?? '', $row['city'] ?? '']);
            $address      = implode(', ', $addressParts) ?: null;

            // Create user
            $fullName = trim("$firstName $lastName");
            $user = User::create([
                'name'      => $fullName,
                'email'     => $email,
                'password'  => Hash::make('Fellow@2024'),
                'user_type' => 7,
            ]);

            UserRole::create([
                'user_id'   => $user->id,
                'role_type' => 7,
                'is_active' => 1,
            ]);

            FellowsModel::create([
                'user_id'          => $user->id,
                'firstname'        => $firstName,
                'lastname'         => $lastName,
                'personal_email'   => $row['email2'] ?? null,
                'gender'           => $this->mapGender($row['title'] ?? ''),
                'status'           => 'Active',
                'phone_number'     => $row['phone'] ?? null,
                'organization'     => $row['organisation'] ?? null,
                'current_specialty'=> $row['jobTitle'] ?? null,
                'address'          => $address,
                'country_id'       => $countryId,
                'profile_image'    => $profileImagePath,
                'category_id'      => 5, // Fellow by Examination (default)
                'is_promoted'      => '0',
            ]);

            $imported++;
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total processed', $total],
                ['Imported',        $imported],
                ['Skipped (duplicate or no name)', $skipped],
                ['No email (placeholder used)',    $noEmail],
                ['Profile images saved',           $imgSaved],
            ]
        );

        return 0;
    }

    private function mapGender(string $title): ?string
    {
        $title = strtolower(trim($title));
        if (in_array($title, ['mr', 'mr.', 'sir', 'prof', 'dr'])) return null; // unknown
        if (in_array($title, ['mrs', 'ms', 'miss', 'dr.'])) return 'Female';
        return null;
    }
}
