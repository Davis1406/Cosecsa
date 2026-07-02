<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CapsuleCrmService
{
    protected string $baseUrl = 'https://api.capsulecrm.com/api/v2';
    protected string $token;

    public function __construct()
    {
        $this->token = config('services.capsule.token', '');
    }

    protected function http()
    {
        return Http::withToken($this->token)
            ->withHeaders(['Accept' => 'application/json'])
            ->timeout(30);
    }

    /**
     * Search for a contact by email address. Returns the first matching party or null.
     */
    public function findByEmail(string $email): ?array
    {
        $response = $this->http()->get("{$this->baseUrl}/parties", [
            'q'       => $email,
            'embed'   => 'tags',
            'perPage' => 10,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $parties = $response->json('parties', []);

        foreach ($parties as $party) {
            $emails = $party['emailAddresses'] ?? [];
            foreach ($emails as $e) {
                if (strtolower($e['address'] ?? '') === strtolower($email)) {
                    return $party;
                }
            }
        }

        return null;
    }

    /**
     * Search for a contact by full name. Returns the first matching party or null.
     */
    public function findByName(string $firstName, string $lastName): ?array
    {
        $q = trim("{$firstName} {$lastName}");
        if (! $q) {
            return null;
        }

        $response = $this->http()->get("{$this->baseUrl}/parties", [
            'q'       => $q,
            'embed'   => 'tags',
            'perPage' => 5,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $parties = $response->json('parties', []);

        foreach ($parties as $party) {
            $pFirst = strtolower($party['firstName'] ?? '');
            $pLast  = strtolower($party['lastName']  ?? '');
            if ($pFirst === strtolower($firstName) && $pLast === strtolower($lastName)) {
                return $party;
            }
        }

        return null;
    }

    /**
     * Fuzzy name search: search Capsule by last name alone, then check if the
     * first name from Capsule starts with the MIS first name (or vice versa).
     * Handles "J Mallya" vs "J S Mallya" and "S Hirwa" vs "S Mutabi Hirwa".
     */
    public function findByNameFuzzy(string $firstName, string $lastName): ?array
    {
        $lastName = trim($lastName);
        if (! $lastName) {
            return null;
        }

        $response = $this->http()->get("{$this->baseUrl}/parties", [
            'q'       => $lastName,
            'embed'   => 'tags',
            'perPage' => 10,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $misFn = strtolower(trim($firstName));
        $misLn = strtolower($lastName);

        foreach ($response->json('parties', []) as $party) {
            $capFn = strtolower(trim($party['firstName'] ?? ''));
            $capLn = strtolower(trim($party['lastName']  ?? ''));

            // Last name must contain or be contained by the MIS last name
            $lnMatch = ($capLn === $misLn)
                || str_contains($capLn, $misLn)
                || str_contains($misLn, $capLn);

            if (! $lnMatch) {
                continue;
            }

            // First name: one must start with the other (handles initials like "J" vs "J S")
            $fnMatch = ($capFn === $misFn)
                || str_starts_with($capFn, $misFn)
                || str_starts_with($misFn, $capFn);

            if ($fnMatch) {
                return $party;
            }
        }

        return null;
    }

    /**
     * Create a new person contact.
     */
    public function createContact(array $data): ?array
    {
        $response = $this->http()->post("{$this->baseUrl}/parties", [
            'party' => $data,
        ]);

        if (! $response->successful()) {
            Log::error('Capsule createContact failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'data'   => $data,
            ]);
            return null;
        }

        return $response->json('party');
    }

    /**
     * Update an existing contact.
     */
    public function updateContact(int $id, array $data): bool
    {
        $response = $this->http()->put("{$this->baseUrl}/parties/{$id}", [
            'party' => $data,
        ]);

        if (! $response->successful()) {
            Log::error('Capsule updateContact failed', [
                'id'     => $id,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }

        return $response->successful();
    }

    /**
     * Get the total number of contacts in Capsule CRM.
     * Capsule API has no total-count endpoint, so we paginate at 100/page and count.
     * Result is cached for 1 hour to avoid hammering the API on every page load.
     */
    public function getTotalContacts(): ?int
    {
        return \Illuminate\Support\Facades\Cache::remember('capsule_total_contacts', 3600, function () {
            $total = 0;
            $page  = 1;
            do {
                $response = $this->http()->get("{$this->baseUrl}/parties", [
                    'perPage' => 100,
                    'page'    => $page,
                ]);
                if (! $response->successful()) {
                    return null;
                }
                $records = $response->json('parties', []);
                $total  += count($records);
                $hasMore = $response->header('X-Pagination-Has-More') === 'true';
                $page++;
                usleep(50000); // 50ms between pages — well within rate limit
            } while ($hasMore);

            return $total;
        });
    }

    /**
     * Get list of all tags in the account.
     */
    public function getTags(): array
    {
        $response = $this->http()->get("{$this->baseUrl}/tags", ['perPage' => 100]);
        return $response->successful() ? $response->json('tags', []) : [];
    }

    /**
     * Add a tag to a contact (POST /parties/{id}/tags).
     */
    public function addTag(int $partyId, string $tagName): bool
    {
        $response = $this->http()->post("{$this->baseUrl}/parties/{$partyId}/tags", [
            'tags' => [['name' => $tagName]],
        ]);
        return $response->successful();
    }

    /**
     * Replace all tags on a contact with the given list.
     */
    public function setTags(int $partyId, array $tagNames): bool
    {
        $tags = array_map(fn($t) => ['name' => $t], $tagNames);
        $response = $this->http()->put("{$this->baseUrl}/parties/{$partyId}/tags", [
            'tags' => $tags,
        ]);
        return $response->successful();
    }

    /**
     * Build a Capsule party payload from a fellow record.
     */
    public static function fellowToPayload(object $fellow): array
    {
        $payload = [
            'type'      => 'person',
            'firstName' => $fellow->firstname ?? '',
            'lastName'  => $fellow->lastname  ?? '',
        ];

        if (! empty($fellow->personal_email)) {
            $payload['emailAddresses'] = [[
                'type'    => 'Work',
                'address' => $fellow->personal_email,
            ]];
        }

        if (! empty($fellow->phone_number)) {
            $payload['phoneNumbers'] = [[
                'type'   => 'Work',
                'number' => $fellow->phone_number,
            ]];
        }

        if (! empty($fellow->organization)) {
            $payload['jobTitle'] = $fellow->current_specialty ?? '';
            $payload['organisation'] = ['name' => $fellow->organization];
        }

        $address = [];
        if (! empty($fellow->country_name)) {
            $address['country'] = $fellow->country_name;
        }
        if (! empty($fellow->address)) {
            $address['street'] = $fellow->address;
        }
        if ($address) {
            $payload['addresses'] = [array_merge(['type' => 'Work'], $address)];
        }

        return $payload;
    }

    /**
     * Build the tag list for a fellow from MIS data.
     */
    public static function fellowTags(object $fellow): array
    {
        $tags = [];

        // Category-based tag
        $categoryTagMap = [
            'Member'                => 'member/fellow',
            'Associate Fellow'      => 'Associate Fellow',
            'Affiliate Member'      => 'Affiliate Member',
            'Associate Member'      => 'Associate Member',
            'Fellow by Examination' => 'Fellow',
            'Foundation Fellow'     => 'Fellow',
            'Fellow by Election'    => 'Fellow',
            'Honorary Fellow (ASEA)'    => 'Honorary Fellow',
            'Overseas Fellow'       => 'Overseas Fellow',
            'Honorary Fellow (COSECSA)' => 'Honorary Fellow',
        ];

        $category = $fellow->category_name ?? '';
        if (isset($categoryTagMap[$category])) {
            $tags[] = $categoryTagMap[$category];
        }

        // Region tag
        if (! empty($fellow->cosecsa_region)) {
            $tags[] = $fellow->cosecsa_region;
        }

        // Deceased
        if (($fellow->status ?? '') === 'Deceased') {
            $tags[] = 'Deceased Fellow';
        }

        // Promoted (FCS)
        if (($fellow->is_promoted ?? '0') === '1') {
            $tags[] = 'FCS Fellow';
        }

        return array_unique($tags);
    }
}
