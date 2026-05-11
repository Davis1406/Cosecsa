<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EnrichFellowsFromJson extends Command
{
    protected $signature   = 'fellows:enrich-json {file : Path to the capsule_fellows.json file}';
    protected $description = 'Enrich fellows DB (admission_year, prog_entry_fee_*) from a JSON file exported from Capsule CRM';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        $capsuleRecords = json_decode(file_get_contents($file), true);
        if (!is_array($capsuleRecords)) {
            $this->error("Invalid JSON in file.");
            return 1;
        }

        $this->info("Loaded " . count($capsuleRecords) . " Capsule records from JSON.");

        // ── Build lookups ────────────────────────────────────────────────────
        $byId       = [];   // capsule party id → rec
        $byEmail    = [];
        $byFullName = [];
        $byNamePair = [];

        foreach ($capsuleRecords as $rec) {
            // Primary: by Capsule party ID
            if (!empty($rec['id'])) {
                $byId[(int) $rec['id']] = $rec;
            }

            $email = strtolower(trim($rec['email'] ?? ''));
            if ($email) {
                $byEmail[$email] = $rec;
            }

            $fn   = strtolower(trim($rec['firstName'] ?? ''));
            $ln   = strtolower(trim($rec['lastName']  ?? ''));
            $full = trim("$fn $ln");
            if ($full) {
                $byFullName[$full] = $rec;
            }
            if ($fn && $ln) {
                $byNamePair["$fn|$ln"] = $rec;
                $byNamePair["$ln|$fn"] = $rec;
            }
        }

        // ── Load fellows from DB ─────────────────────────────────────────────
        $fellows = DB::table('fellows as f')
            ->join('users as u', 'u.id', '=', 'f.user_id')
            ->select(
                'f.id', 'f.firstname', 'f.lastname', 'f.personal_email',
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

        $updated     = 0;
        $intakeSet   = 0;
        $entryFeeSet = 0;

        foreach ($fellows as $f) {
            $bar->advance();

            $userEmail = strtolower(trim($f->user_email ?? ''));
            $persEmail = strtolower(trim($f->personal_email ?? ''));
            $fn        = strtolower(trim($f->firstname ?? ''));
            $ln        = strtolower(trim($f->lastname  ?? ''));
            $fullName  = trim("$fn $ln");

            // ── Match Capsule record ─────────────────────────────────────────
            $rec = null;

            // 1. Extract Capsule party ID from noemail.{id}@capsule.import
            if (preg_match('/^noemail\.(\d+)@capsule\.import$/i', $userEmail, $m)) {
                $rec = $byId[(int) $m[1]] ?? null;
            }

            // 2. Real email match
            if (!$rec && $userEmail && strpos($userEmail, '@capsule.import') === false && isset($byEmail[$userEmail])) {
                $rec = $byEmail[$userEmail];
            }
            if (!$rec && $persEmail && isset($byEmail[$persEmail])) {
                $rec = $byEmail[$persEmail];
            }

            // 3. Name match
            if (!$rec && $fullName && isset($byFullName[$fullName])) {
                $rec = $byFullName[$fullName];
            }
            if (!$rec && $fn && $ln) {
                $rec = $byNamePair["$fn|$ln"] ?? $byNamePair["$ln|$fn"] ?? null;
            }

            if (!$rec) continue;

            $update = ['updated_at' => now()];

            // ── Intake year ──────────────────────────────────────────────────
            if (empty($f->admission_year) && !empty($rec['intakeYear'])) {
                $update['admission_year'] = (int) $rec['intakeYear'];
                $intakeSet++;
            }

            // ── Programme Entry Fee fields ───────────────────────────────────
            if (empty($f->prog_entry_fee_year) && !empty($rec['fee_year'])) {
                $yr = $rec['fee_year'];
                // Handle "Paid 2018", "2018", "2019 Intake", etc.
                if (preg_match('/\b(20\d{2})\b/', $yr, $ym)) {
                    $update['prog_entry_fee_year'] = (int) $ym[1];
                } elseif (is_numeric($yr)) {
                    $update['prog_entry_fee_year'] = (int) $yr;
                }
            }
            if (empty($f->prog_entry_fee_amount_paid) && !empty($rec['fee_amount'])) {
                $amt = $rec['fee_amount'];
                if (is_numeric($amt)) $update['prog_entry_fee_amount_paid'] = (float) $amt;
            }
            if (empty($f->prog_entry_mode_payment) && !empty($rec['fee_mode'])) {
                $update['prog_entry_mode_payment'] = $rec['fee_mode'];
            }

            if (
                isset($update['prog_entry_fee_year']) ||
                isset($update['prog_entry_fee_amount_paid']) ||
                isset($update['prog_entry_mode_payment'])
            ) {
                $entryFeeSet++;
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
                ['Fellows updated',        $updated],
                ['Intake years set',       $intakeSet],
                ['Entry fee records set',  $entryFeeSet],
            ]
        );

        return 0;
    }
}
