<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillTraineesFromCandidates extends Command
{
    protected $signature   = 'trainees:backfill-from-candidates
                                {--dry-run : Preview how many rows would be updated without making changes}';
    protected $description = 'One-time backfill: update trainees shared fields from the more up-to-date candidates table (matched by user_id).';

    /**
     * Normalise candidates.mode_of_payment (varchar) to the ENUM values
     * allowed by trainees.mode_of_payment.
     * Allowed: 'Country Rep', 'Bank transfer', 'Online Payment System', ''
     */
    private function normaliseMOP(?string $raw): string
    {
        if (empty($raw)) {
            return '';
        }
        $lower = strtolower(trim($raw));
        if (str_contains($lower, 'online')) {
            return 'Online Payment System';
        }
        if (str_contains($lower, 'bank')) {
            return 'Bank transfer';
        }
        if (str_contains($lower, 'country') || str_contains($lower, 'rep')) {
            return 'Country Rep';
        }
        // Unrecognised value — leave blank rather than truncate
        return '';
    }

    /** Fields copied from candidates → trainees */
    private const SHARED_FIELDS = [
        'firstname',
        'middlename',
        'lastname',
        'personal_email',
        'gender',
        'programme_id',
        'hospital_id',
        'country_id',
        'entry_number',
        'sponsor',
        'exam_year',
        'invoice_number',
        'invoice_date',
        'invoice_status',
        'amount_paid',
        'payment_date',
        'mode_of_payment',
    ];

    /**
     * FK columns that must have a positive integer value to be safe to sync.
     * A value of 0 or null in candidates means "unset" — don't overwrite a
     * potentially valid trainee FK with an invalid reference.
     */
    private const FK_FIELDS = ['hospital_id', 'programme_id', 'country_id'];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        // Find all trainees that have a matching candidate (by user_id)
        $pairs = DB::table('trainees as t')
            ->join('candidates as c', 'c.user_id', '=', 't.user_id')
            ->select(
                't.id as trainee_id',
                't.user_id',
                ...array_map(fn ($f) => "c.{$f} as c_{$f}", self::SHARED_FIELDS),
                ...array_map(fn ($f) => "t.{$f} as t_{$f}", self::SHARED_FIELDS)
            )
            ->get();

        if ($pairs->isEmpty()) {
            $this->info('No matching trainee/candidate pairs found. Nothing to do.');
            return 0;
        }

        $this->info("Found {$pairs->count()} trainee/candidate pairs.");

        $updated = 0;
        $skipped = 0;

        foreach ($pairs as $row) {
            $payload = [];

            foreach (self::SHARED_FIELDS as $field) {
                $candidateVal = $row->{"c_{$field}"};
                $traineeVal   = $row->{"t_{$field}"};

                // Normalise mode_of_payment to the trainees ENUM before comparing
                if ($field === 'mode_of_payment') {
                    $candidateVal = $this->normaliseMOP($candidateVal);
                }

                // FK columns: skip if the candidate value is 0 / null (means unset)
                if (in_array($field, self::FK_FIELDS, true) && empty((int) $candidateVal)) {
                    continue;
                }

                // Only update if the candidate value is non-empty and differs
                if ($candidateVal !== null && $candidateVal !== '' && $candidateVal != $traineeVal) {
                    $payload[$field] = $candidateVal;
                }
            }

            if (empty($payload)) {
                $skipped++;
                continue;
            }

            if (!$dryRun) {
                DB::table('trainees')
                    ->where('id', $row->trainee_id)
                    ->update(array_merge($payload, ['updated_at' => now()]));
            }

            $updated++;

            if ($this->getOutput()->isVerbose()) {
                $this->line("  [user_id={$row->user_id}] updating: " . implode(', ', array_keys($payload)));
            }
        }

        $label = $dryRun ? '(DRY RUN) Would update' : 'Updated';
        $this->info("{$label} {$updated} trainee rows. Skipped {$skipped} (already in sync).");

        return 0;
    }
}
