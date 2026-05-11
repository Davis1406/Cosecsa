<?php

namespace App\Imports;

use App\Models\Candidates;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Carbon\Carbon;

class CandidatesImport implements ToModel, WithHeadingRow, WithChunkReading, SkipsEmptyRows
{
    private int $imported = 0;
    private int $skipped  = 0;

    // Process 50 rows at a time — keeps memory low and resets execution clock
    public function chunkSize(): int
    {
        return 50;
    }

    public function model(array $row)
    {
        // ── 1. Build full name ────────────────────────────────────────────
        $firstname  = trim($row['firstname']  ?? '');
        $middlename = trim($row['middlename'] ?? '');
        $lastname   = trim($row['lastname']   ?? '');
        $fullName   = trim("$firstname $middlename $lastname");

        if (!$firstname && !$lastname) {
            return null; // skip blank rows
        }

        // ── 2. Find or create the user ────────────────────────────────────
        $email = trim($row['email'] ?? $row['personal_email'] ?? '');

        $user = null;
        if ($email) {
            $user = User::where('email', $email)->first();
        }
        if (!$user && $fullName) {
            $user = User::where('name', $fullName)->first();
        }

        if (!$user) {
            $user = User::create([
                'name'      => $fullName,
                'email'     => $email ?: null,
                'password'  => Hash::make($row['password'] ?? 'Cosecsa@2026'),
                'user_type' => 3,
            ]);

            DB::table('user_roles')->insert([
                'user_id'    => $user->id,
                'role_type'  => 3,
                'is_active'  => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ── 3. Resolve dates ──────────────────────────────────────────────
        $invoiceDate = $this->parseDate($row['invoice_date']  ?? null);
        $paymentDate = $this->parseDate($row['payment_date']  ?? null);

        // ── 4. Resolve nullable integers ─────────────────────────────────
        $hospitalId  = !empty($row['hospital_id'])    ? (int) $row['hospital_id']   : null;
        $programmeId = !empty($row['programme_id'])   ? (int) $row['programme_id']  : null;
        $countryId   = !empty($row['country_id'])     ? (int) $row['country_id']    : null;
        $groupId     = !empty($row['group_id'])        ? (int) $row['group_id']      : null;
        $examYear    = !empty($row['exam_year'])       ? (string) $row['exam_year']  : date('Y');
        $admYear     = !empty($row['admission_year'])  ? (int) $row['admission_year'] : null;

        // ── 5. Invoice / payment fields ───────────────────────────────────
        $invoiceAmount = !empty($row['invoice_amount']) ? (float) $row['invoice_amount'] : null;
        $amountPaid    = !empty($row['amount_paid'])    ? (float) $row['amount_paid']    : null;
        $invoiceStatus = !empty($row['invoice_status']) ? trim($row['invoice_status'])   : 'Pending';
        $feePaid       = (strtolower(trim($row['fee_paid'] ?? '')) === 'yes') ? 'Yes' : 'No';
        $modeOfPayment = !empty($row['mode_of_payment']) ? trim($row['mode_of_payment']) : null;
        $sponsor       = !empty($row['sponsor'])         ? trim($row['sponsor'])         : null;
        $remarks       = !empty($row['remarks'])         ? trim($row['remarks'])         : null;

        // ── 6. Upsert candidate record ────────────────────────────────────
        $existing = DB::table('candidates')
            ->where('user_id', $user->id)
            ->where('exam_year', $examYear)
            ->first();

        $payload = [
            'user_id'          => $user->id,
            'firstname'        => $firstname,
            'middlename'       => $middlename,
            'lastname'         => $lastname,
            'personal_email'   => trim($row['personal_email'] ?? '') ?: null,
            'entry_number'     => !empty($row['entry_number'])  ? trim($row['entry_number'])  : null,
            'hospital_id'      => $hospitalId,
            'programme_id'     => $programmeId,
            'group_id'         => $groupId,
            'gender'           => !empty($row['gender'])        ? trim($row['gender'])        : null,
            'country_id'       => $countryId,
            'repeat_paper_one' => $this->yesNo($row['repeat_paper_one'] ?? null),
            'repeat_paper_two' => $this->yesNo($row['repeat_paper_two'] ?? null),
            'mmed'             => $this->yesNo($row['mmed']             ?? null),
            'invoice_number'   => !empty($row['invoice_number']) ? trim($row['invoice_number']) : null,
            'invoice_date'     => $invoiceDate,
            'invoice_amount'   => $invoiceAmount,
            'invoice_status'   => $invoiceStatus,
            'fee_paid'         => $feePaid,
            'amount_paid'      => $amountPaid,
            'payment_date'     => $paymentDate,
            'mode_of_payment'  => $modeOfPayment,
            'admission_year'   => $admYear,
            'exam_year'        => $examYear,
            'sponsor'          => $sponsor,
            'remarks'          => $remarks,
            'updated_at'       => now(),
        ];

        if ($existing) {
            DB::table('candidates')->where('id', $existing->id)->update($payload);
            $this->skipped++;
            return null; // handled manually — skip Eloquent insert
        }

        $this->imported++;
        $payload['created_at'] = now();
        return new Candidates($payload);
    }

    public function getImportedCount(): int { return $this->imported; }
    public function getSkippedCount():  int { return $this->skipped;  }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function parseDate($value): ?string
    {
        if (empty($value) || in_array(strtolower(trim((string) $value)), ['null', 'none', ''])) {
            return null;
        }
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }
        $s = trim((string) $value);
        foreach (['d/m/Y', 'm/d/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d'] as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $s)->format('Y-m-d');
            } catch (\Exception $e) {
                // try next format
            }
        }
        return null;
    }

    private function yesNo($value): string
    {
        return (strtolower(trim((string) ($value ?? ''))) === 'yes') ? 'Yes' : 'No';
    }
}
