<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;

/**
 * Bulk-update / bulk-create import for the COSECSA SFS Excel format.
 *
 * Row 1 – section headers  (skipped)
 * Row 2 – column headers   (skipped)
 * Row 3+ – data
 *
 * Col 0  PEN            → entry_number (match key)
 * Col 1  SFS Username   → users.email
 * Col 2  SFS Password   → users.password
 * Col 3  First Name
 * Col 4  Middle Name
 * Col 5  Last Name
 * Col 6  Gender
 * Col 7  Organisation   → hospital_id  (fuzzy/create)
 * Col 8  Country        → country_id
 * Col 9  Email          → personal_email
 * Col 10 Exam Type      → programme_id
 * Col 11 Exam Year      → exam_year
 * Col 12 PE Date Paid   → trainees.payment_date
 * Col 13 PE Amount      → trainees.amount_paid
 * Col 14 Exam Date Paid → candidates.payment_date  (exam_year=2027)
 * Col 15 Exam MOP       → candidates.mode_of_payment
 * Col 16 Exam Amount    → candidates.amount_paid
 * Col 17 Repeat Date    → candidates (repeat_paper_one=Yes)
 * Col 18 Repeat MOP
 * Col 19 Repeat Amount
 */
class TraineesExcelUpdateImport implements ToCollection, WithStartRow
{
    private const PROGRAMME_MAP = [
        'mcs'                                => 10,
        'fcs general surgery'                => 2,
        'fcs cardiothoracic surgery'         => 1,
        'fcs neurosurgery'                   => 3,
        'fcs orthopaedic surgery'            => 4,
        'fcs otorhinolaryngology'            => 5,
        'fcs paediatric surgery'             => 7,
        'fcs plastic surgery'                => 8,
        'fcs urologic surgery'               => 9,
        'fcs paediatric orthopaedic surgery' => 6,
        'fcs breast surgery'                 => 12,
    ];

    // MCS = 2 years, all FCS = 3 years
    private const PROGRAMME_PERIOD = [10 => 2];
    private const DEFAULT_PERIOD   = 3;

    private const COUNTRY_MAP = [
        'angola'                        => 49,
        'botswana'                      => 1,
        'ethiopia'                      => 3,
        'gabon'                         => 17,
        'kenya'                         => 4,
        'malawi'                        => 5,
        'mozambique'                    => 6,
        'niger'                         => 18,
        'nigeria'                       => 37,
        'rwanda'                        => 8,
        'south sudan'                   => 9,
        'tanzania'                      => 11,
        'tanzania, united republic of'  => 11,
        'uganda'                        => 12,
        'zambia'                        => 13,
        'zimbabwe'                      => 14,
    ];

    // ── Report buckets ────────────────────────────────────────────────────────
    private array $updated       = [];
    private array $created       = [];
    private array $examUpdated   = [];
    private array $repeatUpdated = [];

    private array $hospitalCache = [];

    public function __construct()
    {
        $this->hospitalCache = DB::table('hospitals')
            ->get(['id', 'name', 'country_id'])
            ->toArray();
    }

    public function startRow(): int { return 3; }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $pen         = trim((string) ($row[0]  ?? ''));
            $loginEmail  = trim((string) ($row[1]  ?? ''));
            $loginPass   = trim((string) ($row[2]  ?? ''));
            $firstName   = trim((string) ($row[3]  ?? ''));
            $middleName  = trim((string) ($row[4]  ?? ''));
            $lastName    = trim((string) ($row[5]  ?? ''));
            $gender      = trim((string) ($row[6]  ?? ''));
            $org         = trim((string) ($row[7]  ?? ''));
            $country     = trim((string) ($row[8]  ?? ''));
            $email       = trim((string) ($row[9]  ?? ''));
            $programme   = trim((string) ($row[10] ?? ''));
            $examYear    = trim((string) ($row[11] ?? ''));

            $peDateRaw   = $row[12] ?? null;
            $peAmount    = $this->toInt($row[13] ?? null);
            $examDateRaw = $row[14] ?? null;
            $examMop     = trim((string) ($row[15] ?? ''));
            $examAmount  = $this->toInt($row[16] ?? null);
            $repDateRaw  = $row[17] ?? null;
            $repMop      = trim((string) ($row[18] ?? ''));
            $repAmount   = $this->toInt($row[19] ?? null);

            if (!$pen || strlen($pen) < 5) continue;

            // ── Resolve lookups ───────────────────────────────────────────────
            $countryId   = self::COUNTRY_MAP[strtolower($country)] ?? null;
            $programmeId = self::PROGRAMME_MAP[strtolower($programme)] ?? null;
            $hospitalId  = $org ? $this->resolveHospital($org, $countryId) : null;

            $fullName = trim(implode(' ', array_filter([$firstName, $middleName, $lastName])));

            // ── Try to find existing trainee ──────────────────────────────────
            $trainee = DB::table('trainees')
                ->whereRaw('TRIM(entry_number) = ?', [$pen])
                ->first();

            if ($trainee) {
                // ── UPDATE existing ───────────────────────────────────────────
                $resolvedCountry   = $countryId   ?? $trainee->country_id;
                $resolvedProgramme = $programmeId ?? $trainee->programme_id;
                $resolvedHospital  = $hospitalId  ?? $trainee->hospital_id;

                // Update user
                $userUpd = ['updated_at' => now()];
                if ($fullName)   $userUpd['name']     = $fullName;
                if ($loginEmail) $userUpd['email']    = $loginEmail;
                if ($loginPass)  $userUpd['password'] = Hash::make($loginPass);
                DB::table('users')->where('id', $trainee->user_id)->update($userUpd);

                // Update trainee
                $traineeUpd = [
                    'firstname'      => $firstName  ?: $trainee->firstname,
                    'middlename'     => $middleName ?: $trainee->middlename,
                    'lastname'       => $lastName   ?: $trainee->lastname,
                    'personal_email' => $email      ?: $trainee->personal_email,
                    'gender'         => $gender     ?: $trainee->gender,
                    'country_id'     => $resolvedCountry,
                    'programme_id'   => $resolvedProgramme,
                    'hospital_id'    => $resolvedHospital,
                    'exam_year'      => $examYear ? (int) $examYear : $trainee->exam_year,
                    'updated_at'     => now(),
                ];
                $peDate = $this->parseDate($peDateRaw);
                if ($peDate)           $traineeUpd['payment_date'] = $peDate;
                if ($peAmount !== null) { $traineeUpd['amount_paid'] = $peAmount; $traineeUpd['fee_paid'] = 'Yes'; }

                DB::table('trainees')->where('id', $trainee->id)->update($traineeUpd);
                $this->updated[] = ['pen' => $pen, 'name' => trim("$firstName $lastName")];

                // Candidate records
                $this->handleCandidateFees(
                    (object)['user_id' => $trainee->user_id, 'firstname' => $firstName ?: $trainee->firstname,
                             'middlename' => $middleName ?: $trainee->middlename, 'lastname' => $lastName ?: $trainee->lastname,
                             'personal_email' => $email ?: $trainee->personal_email, 'hospital_id' => $resolvedHospital],
                    $pen, $resolvedProgramme, $resolvedHospital, $resolvedCountry, $gender,
                    $examDateRaw, $examMop, $examAmount, $repDateRaw, $repMop, $repAmount
                );

            } else {
                // ── CREATE new trainee ────────────────────────────────────────
                if (!$firstName || !$lastName) continue; // can't create without a name

                $resolvedCountry   = $countryId   ?? 4;  // fallback Kenya
                $resolvedProgramme = $programmeId ?? 10; // fallback MCS
                $resolvedHospital  = $hospitalId  ?? $this->fallbackHospital($resolvedCountry);
                $admissionYear     = $this->admissionYearFromPen($pen);
                $period            = self::PROGRAMME_PERIOD[$resolvedProgramme] ?? self::DEFAULT_PERIOD;

                // Find or create user by login email
                $existingUser = $loginEmail
                    ? DB::table('users')->where('email', $loginEmail)->first()
                    : null;

                if ($existingUser) {
                    $userId = $existingUser->id;
                    DB::table('users')->where('id', $userId)->update([
                        'name'       => $fullName ?: $existingUser->name,
                        'password'   => $loginPass ? Hash::make($loginPass) : $existingUser->password,
                        'updated_at' => now(),
                    ]);
                } else {
                    $userId = DB::table('users')->insertGetId([
                        'name'       => $fullName,
                        'email'      => $loginEmail ?: ('no-email.' . strtolower($pen) . '@import'),
                        'password'   => Hash::make($loginPass ?: uniqid('', true)),
                        'user_type'  => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Ensure trainee role exists
                DB::table('user_roles')->insertOrIgnore([
                    'user_id'    => $userId,
                    'role_type'  => 2,
                    'is_active'  => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Build trainee row
                $traineeRow = [
                    'user_id'                  => $userId,
                    'entry_number'             => $pen,
                    'firstname'                => $firstName,
                    'middlename'               => $middleName ?: null,
                    'lastname'                 => $lastName,
                    'personal_email'           => $email ?: $loginEmail,
                    'gender'                   => $gender ?: null,
                    'programme_id'             => $resolvedProgramme,
                    'hospital_id'              => $resolvedHospital,
                    'country_id'               => $resolvedCountry,
                    'admission_year'           => $admissionYear,
                    'exam_year'                => $examYear ? (int) $examYear : ($admissionYear + $period),
                    'programme_period'         => $period,
                    'training_year'            => null,
                    'status'                   => 'Active',
                    'admission_letter_status'  => 'Pending',
                    'invitation_letter_status' => 'Pending',
                    'invoice_status'           => 'Pending',
                    'amount_paid'              => 0,
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ];

                $peDate = $this->parseDate($peDateRaw);
                if ($peDate)           $traineeRow['payment_date'] = $peDate;
                if ($peAmount !== null) { $traineeRow['amount_paid'] = $peAmount; $traineeRow['fee_paid'] = 'Yes'; }

                $traineeId = DB::table('trainees')->insertGetId($traineeRow);
                $this->created[] = ['pen' => $pen, 'name' => trim("$firstName $lastName")];

                // Candidate records
                $traineeObj = (object)[
                    'user_id' => $userId, 'firstname' => $firstName, 'middlename' => $middleName,
                    'lastname' => $lastName, 'personal_email' => $email ?: $loginEmail,
                    'hospital_id' => $resolvedHospital,
                ];
                $this->handleCandidateFees(
                    $traineeObj, $pen, $resolvedProgramme, $resolvedHospital, $resolvedCountry, $gender,
                    $examDateRaw, $examMop, $examAmount, $repDateRaw, $repMop, $repAmount
                );
            }
        }
    }

    // ── Candidate fee upsert ──────────────────────────────────────────────────

    private function handleCandidateFees(
        object $trainee, string $pen, int $programmeId, int $hospitalId, int $countryId, string $gender,
        mixed $examDateRaw, string $examMop, ?int $examAmount,
        mixed $repDateRaw,  string $repMop,  ?int $repAmount
    ): void {
        // Always upsert a candidate record — trainees sitting 2027 exams must appear
        // in the candidates table even before exam fees are collected.
        $this->upsertCandidate($trainee, $pen, $programmeId, $hospitalId, $countryId, $gender,
            $examDateRaw, $examMop, $examAmount, false);
        if ($examAmount !== null || $examDateRaw) {
            $this->examUpdated[] = $pen;
        }

        // Repeat-paper record only when repeat fee data is present.
        if ($repAmount !== null || $repDateRaw) {
            $this->upsertCandidate($trainee, $pen, $programmeId, $hospitalId, $countryId, $gender,
                $repDateRaw, $repMop, $repAmount, true);
            $this->repeatUpdated[] = $pen;
        }
    }

    private function upsertCandidate(
        object $trainee, string $pen, int $programmeId, int $hospitalId, int $countryId, string $gender,
        mixed $datePaid, string $mop, ?int $amount, bool $isRepeat
    ): void {
        $existing = DB::table('candidates')
            ->whereRaw('TRIM(entry_number) = ?', [$pen])
            ->where('exam_year', '2027')
            ->orderByDesc('id')
            ->first();

        $data = [
            'user_id'        => $trainee->user_id,
            'firstname'      => $trainee->firstname,
            'middlename'     => $trainee->middlename ?? null,
            'lastname'       => $trainee->lastname,
            'personal_email' => $trainee->personal_email ?? null,
            'gender'         => $gender ?: null,
            'programme_id'   => $programmeId,
            'hospital_id'    => $hospitalId,
            'country_id'     => $countryId,
            'entry_number'   => $pen,
            'exam_year'      => '2027',
            'updated_at'     => now(),
        ];

        $date = $this->parseDate($datePaid);
        if ($date)           $data['payment_date']    = $date;
        if ($mop)            $data['mode_of_payment'] = $mop;
        if ($amount !== null) { $data['amount_paid']  = $amount; $data['fee_paid'] = 'Yes'; }
        if ($isRepeat)       $data['repeat_paper_one'] = 'Yes';

        if ($existing) {
            DB::table('candidates')->where('id', $existing->id)->update($data);
        } else {
            DB::table('candidates')->insert($data + [
                'invoice_status' => 'Pending',
                'fee_paid'       => $amount !== null ? 'Yes' : 'No',
                'created_at'     => now(),
            ]);
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function admissionYearFromPen(string $pen): string
    {
        // Format: CC/YYYY/NN  e.g. KE/2023/25
        $parts = explode('/', $pen);
        return (isset($parts[1]) && is_numeric($parts[1])) ? $parts[1] : (string) date('Y');
    }

    private function parseDate(mixed $value): ?string
    {
        if (!$value) return null;
        if ($value instanceof \DateTime) return $value->format('Y-m-d');
        if (is_float($value) || (is_int($value) && $value > 40000)) {
            try { return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d'); }
            catch (\Exception $e) { return null; }
        }
        try { return Carbon::parse((string) $value)->format('Y-m-d'); }
        catch (\Exception $e) { return null; }
    }

    private function toInt(mixed $value): ?int
    {
        if ($value === null || $value === '' || $value === false) return null;
        return is_numeric($value) ? (int) $value : null;
    }

    private function resolveHospital(string $name, ?int $countryId): ?int
    {
        $lower = strtolower($name);
        foreach ($this->hospitalCache as $h) {
            if (strtolower($h->name) === $lower) return $h->id;
        }
        foreach ($this->hospitalCache as $h) {
            $hl = strtolower($h->name);
            if (str_contains($lower, $hl) || str_contains($hl, $lower)) return $h->id;
        }
        // Create hospital if not found
        $newId = DB::table('hospitals')->insertGetId([
            'name'          => $name,
            'country_id'    => $countryId ?? 1,
            'hospital_type' => 1,
            'status'        => 0,
            'is_deleted'    => 0,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
        $this->hospitalCache[] = (object)['id' => $newId, 'name' => $name, 'country_id' => $countryId ?? 1];
        return $newId;
    }

    private function fallbackHospital(?int $countryId): int
    {
        if ($countryId) {
            foreach ($this->hospitalCache as $h) {
                if ($h->country_id == $countryId) return $h->id;
            }
        }
        return $this->hospitalCache[0]->id ?? 1;
    }

    // ── Report accessor ───────────────────────────────────────────────────────

    public function getReport(): array
    {
        return [
            'updated'        => $this->updated,
            'created'        => $this->created,
            'examUpdated'    => $this->examUpdated,
            'repeatUpdated'  => $this->repeatUpdated,
            'totals' => [
                'updated'      => count($this->updated),
                'created'      => count($this->created),
                'examUpdated'  => count($this->examUpdated),
                'repeatUpdated'=> count($this->repeatUpdated),
            ],
        ];
    }
}
