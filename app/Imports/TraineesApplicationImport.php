<?php

namespace App\Imports;

use App\Models\Trainee;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;

class TraineesApplicationImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    // ── Report buckets ────────────────────────────────────────────────────────
    private array $imported   = [];   // new trainees created
    private array $updated    = [];   // existing trainees with updated fields
    private array $rejected   = [];   // Application Status = Rejected (skipped)
    private array $pending    = [];   // Status not Complete — not imported
    private array $incomplete = [];   // unresolvable country / hospital / programme
    private array $errors     = [];   // unexpected exceptions

    // ── Lookup caches ─────────────────────────────────────────────────────────
    private array $countryCache   = [];
    private array $hospitalCache  = [];
    private array $programmeCache = [];

    // Full name→id tables loaded once for fuzzy matching
    private array $allCountries = [];   // [id => country_name]
    private array $allHospitals = [];   // [id => name]

    // Country name aliases: CSV value (lowercase) → canonical DB search string
    // Use this for countries where the CSV name can't fuzzy-match the DB name correctly.
    private array $countryAliases = [
        'congo'                            => 'DRC',
        'democratic republic of congo'     => 'DRC',
        'dr congo'                         => 'DRC',
        'dr. congo'                        => 'DRC',
        'drc'                              => 'DRC',
        'republic of congo'                => 'Congo',
        'côte d\'ivoire'                   => 'Ivory Coast',
        'cote d\'ivoire'                   => 'Ivory Coast',
        'ivory coast'                      => 'Ivory Coast',
        'tanzania, united republic of'     => 'Tanzania',
    ];

    // Programme keyword aliases (CSV name → DB LIKE keyword)
    private array $programmeAliases = [
        'mcs'                    => 'MCS',
        'general surgery'        => 'General Surgery',
        'cardiothoracic'         => 'Cardiothoracic',
        'neurosurgery'           => 'Neurosurgery',
        'orthopaedic'            => 'Orthopaedic',
        'otorhinolaryngology'    => 'ENT',
        'ent'                    => 'ENT',
        'paediatric orthopaedic' => 'Paediatric Orthopaedic',
        'paediatric surgery'     => 'Paediatric Surgery',
        'paediatric'             => 'Paediatric',
        'plastic surgery'        => 'Plastic',
        'upper gastrointestinal' => 'Gastrointestinal',
        'urologic surgery'       => 'Urology',
        'urology'                => 'Urology',
        'breast surgery'         => 'Breast',
    ];

    // Human-readable labels for the "what changed" report
    private array $fieldLabels = [
        'admission_letter_status'  => 'Admission Letter',
        'invitation_letter_status' => 'Invitation Letter',
        'admission_year'           => 'Admission Year',
        'invoice_number'           => 'Invoice #',
        'invoice_date'             => 'Invoice Date',
        'invoice_status'           => 'Invoice Status',
        'sponsor'                  => 'Sponsor',
        'mode_of_payment'          => 'Mode of Payment',
        'amount_paid'              => 'Amount Paid',
        'payment_date'             => 'Date Paid',
        'programme_period'         => 'Programme Period',
        'status'                   => 'Application Status',
    ];

    public function chunkSize(): int { return 100; }

    // ── Bootstrap: load all countries + hospitals once ─────────────────────────

    private function loadLookupTables(): void
    {
        if (empty($this->allCountries)) {
            $this->allCountries = DB::table('countries')
                ->pluck('country_name', 'id')->toArray();
        }
        if (empty($this->allHospitals)) {
            $this->allHospitals = DB::table('hospitals')
                ->pluck('name', 'id')->toArray();
        }
    }

    // ── Main handler ──────────────────────────────────────────────────────────

    public function collection(Collection $rows): void
    {
        $this->loadLookupTables();

        foreach ($rows as $row) {
            try {
                $this->processRow($row->toArray());
            } catch (\Throwable $e) {
                $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                $this->errors[] = [
                    'name'   => $name ?: 'Unknown',
                    'pe'     => $row['pe_number'] ?? '—',
                    'email'  => $row['email'] ?? '',
                    'reason' => $e->getMessage(),
                ];
            }
        }
    }

    // ── Row processor ─────────────────────────────────────────────────────────

    private function processRow(array $row): void
    {
        $firstname  = trim($row['first_name']  ?? '');
        $middlename = trim($row['middle_name'] ?? '');
        $lastname   = trim($row['last_name']   ?? '');
        $fullName   = preg_replace('/\s+/', ' ', trim("$firstname $middlename $lastname"));

        if (!$firstname && !$lastname) return;

        $pe          = trim($row['pe_number']    ?? '');
        $email       = trim($row['email']        ?? '');
        $sfsEmail    = trim($row['sfs_username'] ?? '');
        $loginEmail  = $sfsEmail ?: $email;

        // "Status" column = payment/invoice status (Complete, Invoiced, Deferred, Paid & Deffered, Rejected…)
        // "Application Status" column = enrolment status — NOT used for the import gate
        $payStatus = strtolower(trim($row['status'] ?? ''));

        // ── Rejected — skip entirely ──────────────────────────────────────────
        if ($payStatus === 'rejected') {
            $this->rejected[] = $this->rowMeta($fullName, $pe, $email, $row);
            return;
        }

        // ── Only import Status = Complete ─────────────────────────────────────
        if ($payStatus !== 'complete') {
            $entry           = $this->rowMeta($fullName, $pe, $email, $row);
            $entry['reason'] = 'Status: "' . ($row['status'] ?? 'Unknown') . '" — only Complete are imported';
            $this->pending[] = $entry;
            return;
        }

        $appStatus = trim($row['application_status'] ?? '');

        // ── Build the update payload upfront (used for both new and existing) ──
        $updatePayload = $this->buildUpdatePayload($row);

        // ── Existing trainee by PE number → update ────────────────────────────
        if ($pe) {
            $existing = DB::table('trainees')->where('entry_number', $pe)->first();
            if ($existing) {
                $this->applyUpdate($existing, $updatePayload, $fullName, $pe, $email, $row);
                return;
            }
        }

        // ── Existing trainee by email → update ────────────────────────────────
        if ($loginEmail) {
            $existingUser = User::where('email', $loginEmail)->first();
            if ($existingUser) {
                $existingTrainee = DB::table('trainees')
                    ->where('user_id', $existingUser->id)->first();
                if ($existingTrainee) {
                    $this->applyUpdate($existingTrainee, $updatePayload, $fullName, $pe, $email, $row);
                    return;
                }
            }
        }

        // ── New trainee — validate required fields first ───────────────────────
        if (!$firstname || !$lastname) {
            $entry = $this->rowMeta($fullName ?: '(blank)', $pe, $email, $row);
            $entry['reason'] = 'Missing first or last name';
            $this->incomplete[] = $entry;
            return;
        }

        // ── Resolve country / hospital / programme with fuzzy fallback ─────────
        $countryResult  = $this->resolveCountry($row['country'] ?? '');
        $hospitalResult = $this->resolveHospital($row['organizationhospital'] ?? '');
        $programmeId    = $this->resolveProgramme($row['cosecsa_programme'] ?? $row['programme'] ?? '');

        if (!$countryResult['id'] || !$hospitalResult['id'] || !$programmeId) {
            $missing = [];
            if (!$countryResult['id'])  $missing[] = 'Country "'  . ($row['country'] ?? '') . '"'
                . ($countryResult['best'] ? ' (closest: "' . $countryResult['best'] . '" at ' . round($countryResult['score']) . '%)' : '');
            if (!$hospitalResult['id']) $missing[] = 'Hospital "' . ($row['organizationhospital'] ?? '') . '"'
                . ($hospitalResult['best'] ? ' (closest: "' . $hospitalResult['best'] . '" at ' . round($hospitalResult['score']) . '%)' : '');
            if (!$programmeId)          $missing[] = 'Programme "' . ($row['cosecsa_programme'] ?? '') . '"';

            $entry = $this->rowMeta($fullName, $pe, $email, $row);
            $entry['reason'] = 'No match ≥50% — ' . implode('; ', $missing);
            $this->incomplete[] = $entry;
            return;
        }

        // ── Create user + trainee (wrapped in transaction — if trainee fails, user is rolled back) ──
        $password = trim($row['sfs_password'] ?? '') ?: 'Cosecsa@2026';

        DB::transaction(function () use (
            $fullName, $loginEmail, $password, $email, $row,
            $firstname, $middlename, $lastname, $appStatus,
            $programmeId, $hospitalResult, $countryResult, $pe
        ) {
            $user = User::create([
                'name'      => $fullName,
                'email'     => $loginEmail ?: null,
                'password'  => Hash::make($password),
                'user_type' => 2,
            ]);

            DB::table('user_roles')->insert([
                'user_id'    => $user->id,
                'role_type'  => 2,
                'is_active'  => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Trainee::create([
                'user_id'                  => $user->id,
                'firstname'                => $firstname,
                'middlename'               => $middlename,
                'lastname'                 => $lastname,
                'personal_email'           => $email ?: '',
                'gender'                   => $row['gender'] ?? null,
                'status'                   => $this->normaliseTraineeStatus($appStatus ?: 'Complete'),
                'programme_id'             => $programmeId,
                'hospital_id'              => $hospitalResult['id'],
                'country_id'               => $countryResult['id'],
                'entry_number'             => $pe ?: '',
                'admission_letter_status'  => $this->normaliseLetterStatus($row['admission_letter_sent'] ?? ''),
                'invitation_letter_status' => $this->normaliseLetterStatus($row['invitation_letter_sent'] ?? ''),
                'admission_year'           => $this->parseAdmissionYear($row['admission_year'] ?? $row['programme_start'] ?? ''),
                'exam_year'                => $this->parseExamYear($row['exam_year'] ?? ''),
                'programme_period'         => $this->parseProgrammePeriod($row['programme_period'] ?? ''),
                'invoice_number'           => trim($row['invoice'] ?? '') ?: null,
                'invoice_date'             => $this->parseDate($row['invoice_date'] ?? null),
                'invoice_status'           => $this->normaliseInvoiceStatus($row['status'] ?? ''),
                'sponsor'                  => trim($row['sponsor'] ?? '') ?: null,
                'mode_of_payment'          => $this->normaliseModeOfPayment($row['mode_of_payment'] ?? ''),
                'amount_paid'              => !empty($row['amount_paid']) ? (float) $row['amount_paid'] : $this->defaultAmountPaid($row['cosecsa_programme'] ?? $row['programme'] ?? ''),
                'payment_date'             => $this->parseDate($row['date_paid'] ?? null),
            ]);

            // Sync candidate record for current exam year
            $this->syncCandidate($user->id, $row, $programmeId, $hospitalResult['id'], $countryResult['id'], $pe, $email);
        });

        // Build note for fuzzy-matched fields
        $notes = [];
        if ($countryResult['fuzzy'])  $notes[] = 'Country matched as "' . $countryResult['matched'] . '" (' . round($countryResult['score']) . '%)';
        if ($hospitalResult['fuzzy']) $notes[] = 'Hospital matched as "' . $hospitalResult['matched'] . '" (' . round($hospitalResult['score']) . '%)';

        $entry = $this->rowMeta($fullName, $pe, $email, $row);
        $entry['note'] = implode('; ', $notes);
        $this->imported[] = $entry;
    }

    // ── Candidate sync ────────────────────────────────────────────────────────

    /**
     * Create or update a candidate record for the current exam year.
     * Called after new trainee creation and after existing trainee updates.
     */
    private function syncCandidate(int $userId, array $row, int $programmeId, int $hospitalId, int $countryId, string $pe, string $email): void
    {
        $currentYear = (string) date('Y');
        $examYear    = $this->parseExamYear($row['exam_year'] ?? '');
        $yearStr     = $examYear > 0 ? (string) $examYear : $currentYear;

        // Only sync for the current exam year
        if ($yearStr !== $currentYear) return;

        $existing = DB::table('candidates')->where('user_id', $userId)->first();

        $payload = [
            'entry_number'   => $pe ?: '',
            'programme_id'   => $programmeId,
            'hospital_id'    => $hospitalId,
            'country_id'     => $countryId,
            'personal_email' => $email ?: '',
            'gender'         => $row['gender'] ?? null,
            'exam_year'      => $currentYear,
            'admission_year' => $this->parseAdmissionYear($row['admission_year'] ?? $row['programme_start'] ?? ''),
            'sponsor'        => trim($row['sponsor'] ?? '') ?: null,
            'invoice_number' => trim($row['invoice'] ?? '') ?: null,
            'invoice_date'   => $this->parseDate($row['invoice_date'] ?? null),
            'invoice_status' => $this->normaliseInvoiceStatus($row['status'] ?? ''),
            'amount_paid'    => !empty($row['amount_paid']) ? (int) $row['amount_paid'] : $this->defaultAmountPaid($row['cosecsa_programme'] ?? $row['programme'] ?? ''),
            'payment_date'   => $this->parseDate($row['date_paid'] ?? null),
            'mode_of_payment'=> $this->normaliseModeOfPayment($row['mode_of_payment'] ?? ''),
            'fee_paid'       => 'Yes',
            'updated_at'     => now(),
        ];

        if ($existing) {
            DB::table('candidates')->where('id', $existing->id)->update($payload);
        } else {
            $trainee = DB::table('trainees')->where('user_id', $userId)->first();
            DB::table('candidates')->insert(array_merge($payload, [
                'user_id'    => $userId,
                'firstname'  => $trainee->firstname ?? '',
                'middlename' => $trainee->middlename ?? '',
                'lastname'   => $trainee->lastname ?? '',
                'created_at' => now(),
            ]));
        }
    }

    // ── Update existing trainee ───────────────────────────────────────────────

    private function buildUpdatePayload(array $row): array
    {
        $payload = [];

        $strFields = [
            'admission_letter_status'  => $this->normaliseLetterStatus($row['admission_letter_sent'] ?? ''),
            'invitation_letter_status' => $this->normaliseLetterStatus($row['invitation_letter_sent'] ?? ''),
            'invoice_number'           => trim($row['invoice'] ?? ''),
            'invoice_status'           => $this->normaliseInvoiceStatus($row['status'] ?? ''), // "Status" col = payment status
            'status'                   => $this->normaliseTraineeStatus(trim($row['application_status'] ?? '')), // "Application Status" col
            'sponsor'                  => trim($row['sponsor'] ?? ''),
            'mode_of_payment'          => $this->normaliseModeOfPayment($row['mode_of_payment'] ?? ''),
            'programme_period'         => $this->parseProgrammePeriod($row['programme_period'] ?? ''),
        ];

        foreach ($strFields as $key => $val) {
            if ($val !== '') $payload[$key] = $val;
        }

        if (!empty($row['amount_paid']))  $payload['amount_paid']  = (float) $row['amount_paid'];

        // Admission year — prefer the dedicated column, fall back to Programme Start
        $admYear = $this->parseAdmissionYear($row['admission_year'] ?? $row['programme_start'] ?? '');
        if ($admYear) $payload['admission_year'] = $admYear;

        $invoiceDate = $this->parseDate($row['invoice_date'] ?? null);
        $paymentDate = $this->parseDate($row['date_paid'] ?? null);
        if ($invoiceDate) $payload['invoice_date'] = $invoiceDate;
        if ($paymentDate) $payload['payment_date'] = $paymentDate;

        return $payload;
    }

    private function applyUpdate(object $existing, array $payload, string $name, string $pe, string $email, array $row): void
    {
        $changes   = [];
        $toUpdate  = [];

        foreach ($payload as $field => $newVal) {
            $oldVal = $existing->{$field} ?? null;
            // Only write if value actually changed and new value is not empty
            if ((string) $newVal !== (string) $oldVal && $newVal !== null && $newVal !== '') {
                $toUpdate[$field] = $newVal;
                $label    = $this->fieldLabels[$field] ?? $field;
                $changes[] = $label . ': ' . ($oldVal ?: '—') . ' → ' . $newVal;
            }
        }

        if (!empty($toUpdate)) {
            $toUpdate['updated_at'] = now();
            DB::table('trainees')->where('id', $existing->id)->update($toUpdate);
        }

        // Sync candidate record for current exam year
        $this->syncCandidate(
            $existing->user_id,
            $row,
            $existing->programme_id,
            $existing->hospital_id,
            $existing->country_id,
            $pe,
            $email
        );

        $entry           = $this->rowMeta($name, $pe, $email, $row);
        $entry['changes'] = $changes;
        $entry['note']    = empty($changes) ? 'No changes needed' : implode(' | ', $changes);
        $this->updated[] = $entry;
    }

    // ── Resolvers with fuzzy fallback ─────────────────────────────────────────

    /**
     * Returns ['id' => int|null, 'matched' => string, 'best' => string, 'score' => float, 'fuzzy' => bool]
     */
    private function resolveCountry(string $name): array
    {
        $name = trim($name);
        $null = ['id' => null, 'matched' => $name, 'best' => '', 'score' => 0, 'fuzzy' => false];
        if (!$name) return $null;
        if (isset($this->countryCache[$name])) return $this->countryCache[$name];

        // 0. Hardcoded alias table — catches mismatches fuzzy can't resolve (e.g. Congo→DRC)
        $aliasKey = strtolower($name);
        if (isset($this->countryAliases[$aliasKey])) {
            $aliasSearch = $this->countryAliases[$aliasKey];
            $row = DB::table('countries')
                ->whereRaw('LOWER(country_name) = ?', [strtolower($aliasSearch)])
                ->orWhereRaw('LOWER(country_name) LIKE ?', ['%'.strtolower($aliasSearch).'%'])
                ->first(['id','country_name']);
            if ($row) {
                return $this->countryCache[$name] = ['id' => $row->id, 'matched' => $row->country_name, 'best' => '', 'score' => 100, 'fuzzy' => false];
            }
        }

        // 1. Exact
        $row = DB::table('countries')->whereRaw('LOWER(country_name) = ?', [strtolower($name)])->first(['id','country_name']);
        if ($row) return $this->countryCache[$name] = ['id' => $row->id, 'matched' => $row->country_name, 'best' => '', 'score' => 100, 'fuzzy' => false];

        // 2. DB name contains the CSV value (e.g. "Congo" inside "Democratic Republic of Congo")
        $row = DB::table('countries')->whereRaw('LOWER(country_name) LIKE ?', ['%'.strtolower($name).'%'])->first(['id','country_name']);
        if ($row) return $this->countryCache[$name] = ['id' => $row->id, 'matched' => $row->country_name, 'best' => '', 'score' => 100, 'fuzzy' => false];

        // 3. Fuzzy ≥ 50 %
        [$fuzzyId, $fuzzyScore, $fuzzyName] = $this->fuzzyBest($name, $this->allCountries, 50);
        if ($fuzzyId) return $this->countryCache[$name] = ['id' => $fuzzyId, 'matched' => $fuzzyName, 'best' => $fuzzyName, 'score' => $fuzzyScore, 'fuzzy' => true];

        // Not found — report the closest match found (even below threshold) for the report
        [, $bestScore, $bestName] = $this->fuzzyBest($name, $this->allCountries, 0);
        return $this->countryCache[$name] = ['id' => null, 'matched' => $name, 'best' => $bestName, 'score' => $bestScore, 'fuzzy' => false];
    }

    private function resolveHospital(string $name): array
    {
        $name = trim($name);
        $null = ['id' => null, 'matched' => $name, 'best' => '', 'score' => 0, 'fuzzy' => false];
        if (!$name) return $null;
        if (isset($this->hospitalCache[$name])) return $this->hospitalCache[$name];

        // 1. Exact
        $row = DB::table('hospitals')->whereRaw('LOWER(name) = ?', [strtolower($name)])->first(['id','name']);
        if ($row) return $this->hospitalCache[$name] = ['id' => $row->id, 'matched' => $row->name, 'best' => '', 'score' => 100, 'fuzzy' => false];

        // 2. DB name contains CSV value
        $row = DB::table('hospitals')->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($name).'%'])->first(['id','name']);
        if ($row) return $this->hospitalCache[$name] = ['id' => $row->id, 'matched' => $row->name, 'best' => '', 'score' => 100, 'fuzzy' => false];

        // 3. CSV value contains DB name (e.g. "Kenyatta National Hospital Nairobi" contains "Kenyatta National Hospital")
        $all = DB::table('hospitals')->get(['id','name']);
        foreach ($all as $h) {
            if (stripos($name, $h->name) !== false) {
                return $this->hospitalCache[$name] = ['id' => $h->id, 'matched' => $h->name, 'best' => '', 'score' => 100, 'fuzzy' => false];
            }
        }

        // 4. Fuzzy ≥ 85% — raised from 50% to prevent wrong matches like
        //    "Nkinga Referral Hospital" (81%) → "Ayder Referral Hospital" (unrelated institution).
        //    St.Lukes typo ("Hotel" vs "Hospital") still passes at ~94%.
        [$fuzzyId, $fuzzyScore, $fuzzyName] = $this->fuzzyBest($name, $this->allHospitals, 85);
        if ($fuzzyId) return $this->hospitalCache[$name] = ['id' => $fuzzyId, 'matched' => $fuzzyName, 'best' => $fuzzyName, 'score' => $fuzzyScore, 'fuzzy' => true];

        [, $bestScore, $bestName] = $this->fuzzyBest($name, $this->allHospitals, 0);
        return $this->hospitalCache[$name] = ['id' => null, 'matched' => $name, 'best' => $bestName, 'score' => $bestScore, 'fuzzy' => false];
    }

    private function resolveProgramme(string $name): ?int
    {
        $name = trim($name);
        if (!$name) return null;
        if (isset($this->programmeCache[$name])) return $this->programmeCache[$name];

        // Exact
        $id = DB::table('programmes')->whereRaw('LOWER(name) = ?', [strtolower($name)])->value('id');

        // Strip "FCS " and retry
        if (!$id) {
            $short = trim(preg_replace('/^FCS\s+/i', '', $name));
            $id = DB::table('programmes')->whereRaw('LOWER(name) = ?', [strtolower($short)])->value('id')
               ?? DB::table('programmes')->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($short).'%'])->value('id');
        }

        // Keyword alias fallback
        if (!$id) {
            $lower = strtolower($name);
            foreach ($this->programmeAliases as $keyword => $dbKeyword) {
                if (str_contains($lower, $keyword)) {
                    $id = DB::table('programmes')->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($dbKeyword).'%'])->value('id');
                    if ($id) break;
                }
            }
        }

        return $this->programmeCache[$name] = $id;
    }

    // ── Fuzzy match engine ────────────────────────────────────────────────────

    /**
     * Find best match in [id => name] array using similar_text() in both directions.
     * Returns [id|null, score, name] — id is null if best score < $threshold.
     */
    private function fuzzyBest(string $needle, array $haystack, int $threshold): array
    {
        $bestScore = 0;
        $bestId    = null;
        $bestName  = '';
        $lower     = strtolower(trim($needle));

        foreach ($haystack as $id => $candidateName) {
            $candidate = strtolower($candidateName);
            similar_text($lower, $candidate, $pct1);
            similar_text($candidate, $lower, $pct2);
            $score = max($pct1, $pct2);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestId    = $id;
                $bestName  = $candidateName;
            }
        }

        return $bestScore >= $threshold
            ? [$bestId,  $bestScore, $bestName]
            : [null,     $bestScore, $bestName];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Common row metadata used in every report bucket */
    private function rowMeta(string $name, string $pe, string $email, array $row): array
    {
        return [
            'name'      => $name,
            'pe'        => $pe,
            'email'     => $email,
            'programme' => $row['cosecsa_programme'] ?? '',
            'country'   => $row['country'] ?? '',
            'hospital'  => $row['organizationhospital'] ?? '',
            'reason'    => '',
            'note'      => '',
        ];
    }

    private function parseDate($value): ?string
    {
        if (empty($value)) return null;
        if ($value instanceof \DateTime) return $value->format('Y-m-d');
        $s = trim((string) $value);
        if (!$s || $s === '0') return null;
        foreach (['d/m/Y', 'm/d/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d', 'd.m.Y', 'd/m/y'] as $fmt) {
            try { return Carbon::createFromFormat($fmt, $s)->format('Y-m-d'); } catch (\Exception $e) {}
        }
        try { return Carbon::parse($s)->format('Y-m-d'); } catch (\Exception $e) {}
        return null;
    }

    private function parseYear($value): int
    {
        if (preg_match('/\b(20\d{2})\b/', (string) $value, $m)) return (int) $m[1];
        return (int) date('Y');
    }

    private function parseAdmissionYear($value): ?int
    {
        if (preg_match('/\b(20\d{2})\b/', (string) $value, $m)) return (int) $m[1];
        return null;
    }

    /**
     * Normalise the Application Status column to the system's trainee status values.
     * 'Complete' / 'Approved' / 'Enrolled' → 'Active'
     * Everything else is passed through as-is (e.g. 'Deferred', 'Inactive').
     */
    private function normaliseTraineeStatus(string $raw): string
    {
        $v = strtolower(trim($raw));
        if (in_array($v, ['complete', 'approved', 'enrolled'], true)) return 'Active';
        if ($v === '') return '';
        return ucfirst($raw); // preserve original casing for other values
    }

    /**
     * Normalise invoice/payment status to ENUM('Pending','Sent').
     * 'Complete'/'Paid'/'Sent' → 'Sent' | anything else (blank, 'Invoiced', etc.) → 'Pending'
     */
    private function normaliseInvoiceStatus(string $raw): string
    {
        $v = strtolower(trim($raw));
        return in_array($v, ['complete', 'paid', 'sent'], true) ? 'Sent' : 'Pending';
    }

    /**
     * Normalise letter status to ENUM('Pending','Sent').
     * 'Yes'/'Sent' → 'Sent' | anything else (including 'No', blank) → 'Pending'
     */
    private function normaliseLetterStatus(string $raw): string
    {
        $v = strtolower(trim($raw));
        return ($v === 'sent' || $v === 'yes') ? 'Sent' : 'Pending';
    }

    /**
     * Normalise CSV mode-of-payment string to a valid DB ENUM value.
     * ENUM: 'Country Rep' | 'Bank transfer' | 'Online Payment System' | ''
     */
    private function normaliseModeOfPayment(string $raw): string
    {
        $map = [
            'bank transfer'        => 'Bank transfer',
            'bank'                 => 'Bank transfer',
            'country office'       => 'Country Rep',
            'country rep'          => 'Country Rep',
            'country'              => 'Country Rep',
            'online payment'       => 'Online Payment System',
            'online payment system'=> 'Online Payment System',
            'online'               => 'Online Payment System',
        ];
        $key = strtolower(trim($raw));
        return $map[$key] ?? ($key === '' ? 'Bank transfer' : 'Bank transfer');
    }

    /**
     * Default amount paid by programme type.
     * MCS → 500 | FCS (any speciality) → 600
     */
    private function defaultAmountPaid(string $programme): int
    {
        $p = strtolower(trim($programme));
        if (str_starts_with($p, 'fcs')) return 600;
        return 500; // MCS and anything else
    }

    /**
     * Parse exam year for NEW trainees only.
     * Returns the year from the CSV or 0 if blank (do not default to current year).
     */
    private function parseExamYear($value): int
    {
        if (preg_match('/\b(20\d{2})\b/', (string) $value, $m)) return (int) $m[1];
        return 0;
    }

    /**
     * Parse programme period to an integer (years).
     * Handles "2 Years", "3 years", "1Year", "1  Year", plain "2", etc.
     */
    private function parseProgrammePeriod($value): int
    {
        $s = trim((string) $value);
        if (preg_match('/^(\d+)/', $s, $m)) return (int) $m[1];
        return 0; // NOT NULL — default 0 when CSV is blank
    }

    // ── Report ────────────────────────────────────────────────────────────────

    public function getReport(): array
    {
        $totals = [
            'imported'   => count($this->imported),
            'updated'    => count($this->updated),
            'rejected'   => count($this->rejected),
            'pending'    => count($this->pending),
            'incomplete' => count($this->incomplete),
            'errors'     => count($this->errors),
        ];
        $totals['total'] = array_sum($totals);

        return [
            'totals'     => $totals,
            'imported'   => $this->imported,
            'updated'    => $this->updated,
            'rejected'   => $this->rejected,
            'pending'    => $this->pending,
            'incomplete' => $this->incomplete,
            'errors'     => $this->errors,
        ];
    }
}
