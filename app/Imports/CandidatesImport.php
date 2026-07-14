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

/**
 * Imports the examination officer's candidates template — columns:
 * First Name, Middle Name, Last Name, Email, PEN, Organisation, Exam Type,
 * Gender, Country, Repeat PI, Repeat PII, MMED, Sponsor, Paid, Remarks,
 * Invoice #, Invoice Date, Amount, Invoice Sent, Fee Paid, Date, Amount Paid,
 * Mode of Payment, Comments.
 */
class CandidatesImport implements ToModel, WithHeadingRow, WithChunkReading, SkipsEmptyRows
{
    private int $imported = 0;
    private int $updated  = 0;
    private array $errors = [];

    // Tanzania is listed as "Tanzania, United Republic of" in the officer's template.
    private const COUNTRY_ALIASES = [
        'tanzania, united republic of' => 'tanzania',
        'united republic of tanzania'  => 'tanzania',
        'drc'                          => 'drc',
        'congo, the democratic republic of the' => 'drc',
    ];

    private ?array $programmesByName = null;
    private ?array $countriesByName  = null;
    private ?array $hospitalsByName  = null;

    public function chunkSize(): int
    {
        return 50;
    }

    public function model(array $row)
    {
        $firstname  = trim($row['first_name']  ?? '');
        $middlename = trim($row['middle_name'] ?? '');
        $lastname   = trim($row['last_name']   ?? '');
        $fullName   = trim("$firstname $middlename $lastname");

        if (!$firstname && !$lastname) {
            return null; // skip blank rows
        }

        $pen   = trim($row['pen']   ?? '') ?: null;
        $email = trim($row['email'] ?? '') ?: null;

        // ── 1. Find or create the user — PEN is the reliable identity key ──
        $user = null;
        if ($pen) {
            $existingByPen = DB::table('candidates')->where('entry_number', $pen)->first();
            if ($existingByPen) {
                $user = User::find($existingByPen->user_id);
            }
        }
        if (!$user && $email) {
            $user = User::where('email', $email)->first();
        }
        if (!$user && $fullName) {
            $user = User::where('name', $fullName)->first();
        }

        if (!$user) {
            $user = User::create([
                'name'      => $fullName,
                'email'     => $email,
                'password'  => Hash::make('Cosecsa@2026'),
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

        // ── 2. Resolve lookups ──────────────────────────────────────────────
        $programmeId = $this->resolveProgramme($row['exam_type'] ?? null, $pen);
        $countryId   = $this->resolveCountry($row['country'] ?? null, $pen);
        $hospitalId  = $this->resolveHospital($row['organisation'] ?? null);

        $gender = strtolower(trim($row['gender'] ?? ''));
        $gender = $gender === 'male' ? 'Male' : ($gender === 'female' ? 'Female' : null);

        // ── 3. Invoice / payment fields ─────────────────────────────────────
        $invoiceNumber = trim($row['invoice'] ?? '') ?: null; // "Invoice #" slugs to "invoice"
        $invoiceDate   = $this->parseDate($row['invoice_date'] ?? null);
        $invoiceAmount = is_numeric($row['amount'] ?? null) ? (int) $row['amount'] : null;
        $feePaid       = $this->yesNo($row['fee_paid'] ?? null);
        $amountPaid    = is_numeric($row['amount_paid'] ?? null) ? (int) $row['amount_paid'] : null;
        $paymentDate   = $this->parseDate($row['date'] ?? null);
        $modeOfPayment = trim($row['mode_of_payment'] ?? '') ?: null;
        $sponsor       = trim($row['sponsor'] ?? '') ?: null;

        $invoiceSent = strtolower(trim($row['invoice_sent'] ?? ''));
        $invoiceStatus = $feePaid === 'Yes'
            ? 'Complete'
            : (in_array($invoiceSent, ['sent', 'yes'], true) ? 'Sent' : 'Pending');

        $remarks = trim(implode(' | ', array_filter([
            trim($row['remarks']  ?? ''),
            trim($row['comments'] ?? ''),
        ])));

        // ── 4. Upsert candidate record, keyed by PEN (entry_number) ────────
        $payload = [
            'user_id'          => $user->id,
            'firstname'        => $firstname,
            'middlename'       => $middlename ?: null,
            'lastname'         => $lastname,
            'personal_email'   => $email,
            'entry_number'     => $pen,
            'hospital_id'      => $hospitalId,
            'programme_id'     => $programmeId,
            'gender'           => $gender,
            'country_id'       => $countryId,
            'repeat_paper_one' => $this->yesFlag($row['repeat_pi']  ?? null),
            'repeat_paper_two' => $this->yesFlag($row['repeat_pii'] ?? null),
            'mmed'             => $this->yesFlag($row['mmed'] ?? null),
            'invoice_number'   => $invoiceNumber,
            'invoice_date'     => $invoiceDate,
            'invoice_amount'   => $invoiceAmount,
            'invoice_status'   => $invoiceStatus,
            'fee_paid'         => $feePaid,
            'amount_paid'      => $amountPaid,
            'payment_date'     => $paymentDate,
            'mode_of_payment'  => $modeOfPayment,
            'exam_year'        => '2026',
            'sponsor'          => $sponsor,
            'remarks'          => $remarks ?: null,
            'updated_at'       => now(),
        ];

        if ($programmeId === null) {
            $this->errors[] = "Row skipped (no programme match for Exam Type '{$row['exam_type']}'): $fullName / $pen";
            return null;
        }

        $existing = $pen
            ? DB::table('candidates')->where('entry_number', $pen)->first()
            : DB::table('candidates')->where('user_id', $user->id)->where('exam_year', '2026')->first();

        if ($existing) {
            DB::table('candidates')->where('id', $existing->id)->update($payload);
            $this->updated++;
            return null;
        }

        $this->imported++;
        $payload['created_at'] = now();
        return new Candidates($payload);
    }

    public function getImportedCount(): int { return $this->imported; }
    public function getUpdatedCount():  int { return $this->updated;  }
    public function getSkippedCount():  int { return $this->updated;  } // kept for controller backward-compat
    public function getErrors(): array { return $this->errors; }

    // ── Lookup resolvers ─────────────────────────────────────────────────────

    private function resolveProgramme(?string $examType, ?string $pen): ?int
    {
        $examType = trim($examType ?? '');
        if (!$examType) return null;

        if ($this->programmesByName === null) {
            $this->programmesByName = [];
            foreach (DB::table('programmes')->get(['id', 'name']) as $p) {
                $this->programmesByName[strtolower(trim($p->name))] = $p->id;
            }
        }

        $key = strtolower($examType);
        if (isset($this->programmesByName[$key])) {
            return $this->programmesByName[$key];
        }

        $this->errors[] = "No programme match for Exam Type '{$examType}' (PEN: {$pen})";
        return null;
    }

    private function resolveCountry(?string $country, ?string $pen): ?int
    {
        $country = trim($country ?? '');
        if (!$country) return null;

        if ($this->countriesByName === null) {
            $this->countriesByName = [];
            foreach (DB::table('countries')->get(['id', 'country_name']) as $c) {
                $this->countriesByName[strtolower(trim($c->country_name))] = $c->id;
            }
        }

        $key = strtolower($country);
        $key = self::COUNTRY_ALIASES[$key] ?? $key;

        if (isset($this->countriesByName[$key])) {
            return $this->countriesByName[$key];
        }

        $this->errors[] = "No country match for '{$country}' (PEN: {$pen})";
        return null;
    }

    private function resolveHospital(?string $organisation): ?int
    {
        $organisation = trim($organisation ?? '');
        if (!$organisation) return null;

        if ($this->hospitalsByName === null) {
            $this->hospitalsByName = [];
            foreach (DB::table('hospitals')->get(['id', 'name']) as $h) {
                $this->hospitalsByName[strtolower(trim($h->name))] = $h->id;
            }
        }

        $key = strtolower($organisation);
        if (isset($this->hospitalsByName[$key])) {
            return $this->hospitalsByName[$key];
        }

        // Fuzzy fallback for minor spelling/punctuation differences
        $best = null; $bestPct = 0;
        foreach (array_keys($this->hospitalsByName) as $name) {
            similar_text($key, $name, $pct);
            if ($pct > $bestPct) { $bestPct = $pct; $best = $name; }
        }
        if ($best !== null && $bestPct >= 88) {
            return $this->hospitalsByName[$best];
        }

        $this->errors[] = "No hospital match for Organisation '{$organisation}'";
        return null;
    }

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

    // For enum columns storing 'Yes'/'No' (fee_paid)
    private function yesNo($value): string
    {
        return (strtolower(trim((string) ($value ?? ''))) === 'yes') ? 'Yes' : 'No';
    }

    // For single-letter flag columns (Repeat PI/PII, MMED use 'Y')
    private function yesFlag($value): string
    {
        return (strtolower(trim((string) ($value ?? ''))) === 'y') ? 'Yes' : 'No';
    }
}
