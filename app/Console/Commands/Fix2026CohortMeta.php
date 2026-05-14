<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Fix2026CohortMeta extends Command
{
    protected $signature = 'trainees:fix-2026-meta {--dry-run : Preview without writing}';
    protected $description = 'Fill admission_year=2026 for /2026/ PENs; mark mmed=Yes for candidates admitted & sitting exams in 2026.';

    public function handle(): int
    {
        $dry = $this->option('dry-run');
        if ($dry) {
            $this->warn('[DRY RUN] No changes will be written.');
        }

        // ── 1. Set admission_year = 2026 for trainees whose PEN contains /2026/ ──
        $traineeRows = DB::table('trainees')
            ->whereRaw("entry_number LIKE '%/2026/%'")
            ->where(function ($q) {
                $q->whereNull('admission_year')
                  ->orWhere('admission_year', 0)
                  ->orWhere('admission_year', '!=', 2026);
            })
            ->count();

        $this->line("Trainees needing admission_year=2026 : {$traineeRows}");

        if (!$dry && $traineeRows > 0) {
            DB::table('trainees')
                ->whereRaw("entry_number LIKE '%/2026/%'")
                ->where(function ($q) {
                    $q->whereNull('admission_year')
                      ->orWhere('admission_year', 0)
                      ->orWhere('admission_year', '!=', 2026);
                })
                ->update(['admission_year' => 2026]);
            $this->info("  ✓ Updated {$traineeRows} trainee(s) admission_year → 2026");
        }

        // ── 2. Mark mmed = 'Yes' on candidates admitted 2026 sitting exams 2026 ──
        $candidateRows = DB::table('candidates')
            ->where('admission_year', 2026)
            ->where('exam_year', 2026)
            ->where(function ($q) {
                $q->where('mmed', '!=', 'Yes')->orWhereNull('mmed');
            })
            ->count();

        $this->line("Candidates (adm 2026, exam 2026) needing mmed=Yes : {$candidateRows}");

        if (!$dry && $candidateRows > 0) {
            DB::table('candidates')
                ->where('admission_year', 2026)
                ->where('exam_year', 2026)
                ->where(function ($q) {
                    $q->where('mmed', '!=', 'Yes')->orWhereNull('mmed');
                })
                ->update(['mmed' => 'Yes']);
            $this->info("  ✓ Updated {$candidateRows} candidate(s) mmed → Yes");
        }

        $this->newLine();
        $this->info('Done.');
        return self::SUCCESS;
    }
}
