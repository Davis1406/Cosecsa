<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;

/**
 * Bulk-update import for the COSECSA SFS "2027 Exam candidates-trainees" Excel format.
 *
 * Expected sheet structure (1-indexed rows):
 *   Row 1  – section headers: "Trainee Details" | "MCS PE Fees" | "MCS Exam Fees" | "MCS Repeat Fees"
 *   Row 2  – column headers:  PEN | SFS Username | SFS Password | First Name | ... | Date Paid | Mode of Payment | Amount Paid
 *   Row 3+ – data
 *
 * Columns (0-based index):
 *   0  PEN (entry_number)
 *   1  SFS Username   → users.email (login)
 *   2  SFS Password   → users.password
 *   3  First Name
 *   4  Middle Name
 *   5  Last Name
 *   6  Gender
 *   7  Organisation   → trainees.hospital_id (fuzzy match)
 *   8  Country        → trainees.country_id
 *   9  Email          → trainees.personal_email
 *  10  Exam Type      → trainees.programme_id
 *  11  Exam Year      → trainees.exam_year
 *  12  MCS PE Fees  – Date Paid
 *  13  MCS PE Fees  – Amount Paid  → trainees.payment_date / amount_paid
 *  14  MCS Exam Fees – Date Paid
 *  15  MCS Exam Fees – Mode of Payment
 *  16  MCS Exam Fees – Amount Paid → candidates (exam_year=2027)
 *  17  MCS Repeat Fees – Date Paid
 *  18  MCS Repeat Fees – Mode of Payment
 *  19  MCS Repeat Fees – Amount Paid → candidates (repeat_paper_one=Yes)
 */
class TraineesExcelUpdateImport implements ToCollection, WithStartRow
{
    // ── Programme name → programme_id ────────────────────────────────────────
    private const PROGRAMME_MAP = [
        'mcs'                         => 10,
        'fcs general surgery'         => 2,
        'fcs cardiothoracic surgery'  => 1,
        'fcs neurosurgery'            => 3,
        'fcs orthopaedic surgery'     => 4,
        'fcs otorhinolaryngology'     => 5,
        'fcs paediatric surgery'      => 7,
        'fcs plastic surgery'         => 8,
        'fcs urologic surgery'        => 9,
        'fcs paediatric orthopaedic surgery' => 6,
        'fcs breast surgery'          => 12,
    ];

    // ── Country name → country_id ─────────────────────────────────────────────
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
    private array $updated       = [];   // trainees updated successfully
    private array $notFound      = [];   // PEN not in trainees table
    private array $examUpdated   = [];   // candidates exam-fee records updated/created
    private array $repeatUpdated = [];   // candidates repeat-fee records updated

    private array $hospitalCache = [];

    public function __construct()
    {
        $this->hospitalCache = DB::table('hospitals')
            ->get(['id', 'name', 'country_id'])
            ->toArray();
    }

    // Skip the two header rows (section headers + column names)
    public function startRow(): int
    {
        return 3;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            // ── Extract raw values ────────────────────────────────────────────
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

            // MCS PE Fees
            $peDateRaw   = $row[12] ?? null;
            $peAmount    = $this->toInt($row[13] ?? null);

            // MCS Exam Fees
            $examDateRaw = $row[14] ?? null;
            $examMop     = trim((string) ($row[15] ?? ''));
            $examAmount  = $this->toInt($row[16] ?? null);

            // MCS Repeat Fees
            $repDateRaw  = $row[17] ?? null;
            $repMop      = trim((string) ($row[18] ?? ''));
            $repAmount   = $this->toInt($row[19] ?? null);

            if (!$pen || strlen($pen) < 5) continue;  // skip blank / header rows

            // ── Find trainee by entry_number ─────────────────────────────────
            $trainee = DB::table('trainees')
                ->whereRaw('TRIM(entry_number) = ?', [$pen])
                ->first();

            if (!$trainee) {
                $this->notFound[] = $pen;
                continue;
            }

            // ── Resolve foreign keys ─────────────────────────────────────────
            $countryId   = self::COUNTRY_MAP[strtolower($country)] ?? $trainee->country_id;
            $programmeId = self::PROGRAMME_MAP[strtolower($programme)] ?? $trainee->programme_id;
            $hospitalId  = $org ? ($this->resolveHospital($org, $countryId) ?? $trainee->hospital_id) : $trainee->hospital_id;

            // ── Update users table ────────────────────────────────────────────
            $fullName = trim(implode(' ', array_filter([$firstName, $middleName, $lastName])));
            $userUpd  = ['updated_at' => now()];
            if ($fullName)    $userUpd['name']     = $fullName;
            if ($loginEmail)  $userUpd['email']    = $loginEmail;
            if ($loginPass)   $userUpd['password'] = Hash::make($loginPass);

            DB::table('users')
                ->where('id', $trainee->user_id)
                ->update($userUpd);

            // ── Update trainees table ─────────────────────────────────────────
            $traineeUpd = [
                'firstname'      => $firstName   ?: $trainee->firstname,
                'middlename'     => $middleName  ?: $trainee->middlename,
                'lastname'       => $lastName    ?: $trainee->lastname,
                'personal_email' => $email       ?: $trainee->personal_email,
                'gender'         => $gender      ?: $trainee->gender,
                'country_id'     => $countryId,
                'programme_id'   => $programmeId,
                'hospital_id'    => $hospitalId,
                'exam_year'      => $examYear ? (int) $examYear : $trainee->exam_year,
                'updated_at'     => now(),
            ];

            // PE fee → lives in the trainees table
            $peDate = $this->parseDate($peDateRaw);
            if ($peDate)      $traineeUpd['payment_date'] = $peDate;
            if ($peAmount !== null) {
                $traineeUpd['amount_paid'] = $peAmount;
                $traineeUpd['fee_paid']    = 'Yes';
            }

            DB::table('trainees')
                ->where('id', $trainee->id)
                ->update($traineeUpd);

            $this->updated[] = [
                'pen'  => $pen,
                'name' => trim("$firstName $lastName"),
            ];

            // ── Upsert candidates record — MCS Exam Fee ───────────────────────
            if ($examAmount !== null || $examDateRaw) {
                $this->upsertCandidate(
                    $trainee, $pen, $programmeId, $hospitalId, $countryId,
                    $firstName, $middleName, $lastName, $email, $gender,
                    $examDateRaw, $examMop, $examAmount,
                    false   // not a repeat record
                );
                $this->examUpdated[] = $pen;
            }

            // ── Upsert candidates record — MCS Repeat Fee ────────────────────
            if ($repAmount !== null || $repDateRaw) {
                $this->upsertCandidate(
                    $trainee, $pen, $programmeId, $hospitalId, $countryId,
                    $firstName, $middleName, $lastName, $email, $gender,
                    $repDateRaw, $repMop, $repAmount,
                    true    // mark as repeat
                );
                $this->repeatUpdated[] = $pen;
            }
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function upsertCandidate(
        object  $trainee,
        string  $pen,
        int     $programmeId,
        int     $hospitalId,
        int     $countryId,
        string  $firstName,
        string  $middleName,
        string  $lastName,
        string  $email,
        string  $gender,
        mixed   $datePaid,
        string  $mop,
        ?int    $amount,
        bool    $isRepeat
    ): void {
        $existing = DB::table('candidates')
            ->whereRaw('TRIM(entry_number) = ?', [$pen])
            ->where('exam_year', '2027')
            ->orderByDesc('id')
            ->first();

        $data = [
            'user_id'        => $trainee->user_id,
            'firstname'      => $firstName  ?: $trainee->firstname,
            'middlename'     => $middleName ?: $trainee->middlename,
            'lastname'       => $lastName   ?: $trainee->lastname,
            'personal_email' => $email      ?: $trainee->personal_email,
            'gender'         => $gender     ?: null,
            'programme_id'   => $programmeId,
            'hospital_id'    => $hospitalId,
            'country_id'     => $countryId,
            'entry_number'   => $pen,
            'exam_year'      => '2027',
            'updated_at'     => now(),
        ];

        $date = $this->parseDate($datePaid);
        if ($date)          $data['payment_date']    = $date;
        if ($mop)           $data['mode_of_payment'] = $mop;
        if ($amount !== null) {
            $data['amount_paid'] = $amount;
            $data['fee_paid']    = 'Yes';
        }
        if ($isRepeat) {
            $data['repeat_paper_one'] = 'Yes';
        }

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

    private function parseDate(mixed $value): ?string
    {
        if (!$value) return null;

        // Maatwebsite Excel returns DateTime / Carbon objects for date cells
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }

        // Fallback: Excel serial number
        if (is_float($value) || (is_int($value) && $value > 40000)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // String date
        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function toInt(mixed $value): ?int
    {
        if ($value === null || $value === '' || $value === false) return null;
        return is_numeric($value) ? (int) $value : null;
    }

    private function resolveHospital(string $name, ?int $countryId): ?int
    {
        $lower = strtolower($name);

        // Exact match
        foreach ($this->hospitalCache as $h) {
            if (strtolower($h->name) === $lower) return $h->id;
        }

        // Partial / substring match
        foreach ($this->hospitalCache as $h) {
            $hl = strtolower($h->name);
            if (str_contains($lower, $hl) || str_contains($hl, $lower)) return $h->id;
        }

        return null;
    }

    // ── Report accessor ───────────────────────────────────────────────────────

    public function getReport(): array
    {
        return [
            'updated'        => $this->updated,
            'notFound'       => $this->notFound,
            'examUpdated'    => $this->examUpdated,
            'repeatUpdated'  => $this->repeatUpdated,
            'totals' => [
                'updated'      => count($this->updated),
                'notFound'     => count($this->notFound),
                'examUpdated'  => count($this->examUpdated),
                'repeatUpdated'=> count($this->repeatUpdated),
            ],
        ];
    }
}
