<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Read side of the "start the exam" guard (the officer starts and locks it under Exam Groups & Stations;
 * the rules and the writes live in cosecsa-api's App\Services\ExamControl). Until an exam is started
 * examiners cannot select it or save real marks for it; the TEST- practice groups stay open.
 */
class ExamControl
{
    public const GUARDED = ['mcs'];

    public static function state(string $exam): array
    {
        $guarded = in_array($exam, self::GUARDED, true);
        $yearId = User::getCurrentYearId();
        $raw = ($yearId && Schema::hasTable('app_settings')) ? DB::table('app_settings')->where('key', "exam_control.$exam.$yearId")->value('value') : null;
        $s = is_string($raw) ? (json_decode($raw, true) ?: []) : [];

        return [
            'exam'            => $exam,
            'guarded'         => $guarded,
            'open'            => ! $guarded || ! empty($s['open']),
            'started_at'      => $s['started_at'] ?? null,
            'started_by_name' => $s['started_by_name'] ?? null,
            'scheduled_for'   => $s['scheduled_for'] ?? null,
        ];
    }

    public static function isOpen(string $exam): bool
    {
        return self::state($exam)['open'];
    }

    public static function isTestGroup($groupId): bool
    {
        return $groupId && strncasecmp((string) DB::table('examiners_groups')->where('id', $groupId)->value('group_name'), 'TEST-', 5) === 0;
    }

    /** Real marks may be saved: the exam is started, or the group is a TEST- practice group. */
    public static function marksAllowed(string $exam, $groupId): bool
    {
        return self::isOpen($exam) || self::isTestGroup($groupId);
    }
}
