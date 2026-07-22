<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // One-time backfill so already-saved rows (including already-submitted
    // reports like Amani's) get the "❖ " per-line bullet retroactively —
    // matching the normalization ProgressiveReportController::updateTask()
    // now applies on every save going forward.
    public function up(): void
    {
        $rows = DB::table('progress_report_tasks')
            ->select('id', 'planned_activities', 'current_status', 'next_steps')
            ->get();

        foreach ($rows as $row) {
            $update = [];
            foreach (['planned_activities', 'current_status', 'next_steps'] as $field) {
                $normalized = self::normalize($row->{$field});
                if ($normalized !== $row->{$field}) {
                    $update[$field] = $normalized;
                }
            }
            if ($update) {
                DB::table('progress_report_tasks')->where('id', $row->id)->update($update);
            }
        }
    }

    public function down(): void
    {
        // Not reversible — the original un-bulleted text isn't recoverable
        // (task revisions retain the pre-normalization history if needed).
    }

    protected static function normalize(?string $text): ?string
    {
        if ($text === null || trim($text) === '') {
            return $text;
        }

        $lines = preg_split('/\r\n|\r|\n/', $text);
        $lines = array_map(function ($line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                return null;
            }
            return str_starts_with($trimmed, '❖') ? $trimmed : '❖ ' . $trimmed;
        }, $lines);

        return implode("\n", array_filter($lines, fn ($l) => $l !== null));
    }
};
