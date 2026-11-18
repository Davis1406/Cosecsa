<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Read side of the FCS form layout (Exams Settings > Form labels): how many cases a Clinical or Viva form has, the
 * heading of each case, the questions of each case with their labels and the marks an examiner can give. The
 * standard layout below is what the forms always had; the rules and the writes live in cosecsa-api's
 * App\Services\ExamFormConfig (keep the two in step). Stored in app_settings (key `exam_form.<exam>.<format>`, JSON).
 *
 * Marks are saved as a flat list plus, where the results table has it, case_layout: how many questions each case had.
 * layout() turns a saved row back into per-case counts; rows without a layout (older ones, the mobile app) are
 * laid out the way the forms always did.
 */
class ExamFormConfig
{
    public const CLINICAL_LABELS = ['Overall Professional Capacity and Patient Care', 'Knowledge and Judgement', 'Quality of Response', 'Bedside Manner'];

    /** How many cases the form had before the layout became editable: the number saved marks without a layout were split by. */
    public static function legacyCases(string $exam, string $format): int
    {
        if ($format === 'viva') {
            return 2;
        }

        return in_array($exam, ['plastic_surgery', 'neurosurgery', 'urology'], true) ? 2 : 1;
    }

    public static function defaults(string $exam, string $format): array
    {
        if ($format === 'viva') {
            $per = $exam === 'paediatric' ? 2 : 3;
            $cases = [
                ['title' => 'Knowledge & Judgement', 'questions' => array_fill(0, $per, '')],
                ['title' => 'Quality of Response', 'questions' => array_fill(0, $per, '')],
            ];
        } else {
            $cases = array_fill(0, self::legacyCases($exam, 'clinical'), ['title' => '', 'questions' => array_slice(self::CLINICAL_LABELS, 0, 3)]);
        }

        return [
            'cases' => $cases,
            'marks' => ($format === 'viva' && in_array($exam, ['urology', 'paediatric'], true)) ? [0, 2, 4, 6, 8, 10] : [2, 4, 6, 8, 10],
            'allow_extra' => ! ($format === 'viva' && $exam === 'paediatric'),
        ];
    }

    /** What the form uses now: the saved layout, or the standard one. */
    public static function get(string $exam, string $format): array
    {
        $defaults = self::defaults($exam, $format);
        $raw = Schema::hasTable('app_settings') ? DB::table('app_settings')->where('key', "exam_form.$exam.$format")->value('value') : null;
        $saved = is_string($raw) ? (json_decode($raw, true) ?: []) : [];

        $cases = [];
        foreach ((array) ($saved['cases'] ?? []) as $c) {
            $qs = array_values(array_map('strval', (array) ($c['questions'] ?? [])));
            $cases[] = ['title' => (string) ($c['title'] ?? ''), 'questions' => $qs ?: ['']];
        }
        $marks = array_values((array) ($saved['marks'] ?? []));

        return [
            'format' => $format,
            'cases' => $cases ?: $defaults['cases'],
            'marks' => $marks ?: $defaults['marks'],
            'allow_extra' => array_key_exists('allow_extra', $saved) ? (bool) $saved['allow_extra'] : $defaults['allow_extra'],
            'legacy_labels' => $format === 'clinical' ? self::CLINICAL_LABELS : [],
        ];
    }

    /** The heading of case $case (1-based): the one set under Form labels, else "Case N". */
    public static function caseTitle(array $cfg, int $case): string
    {
        $t = trim((string) ($cfg['cases'][$case - 1]['title'] ?? ''));

        return $t !== '' ? $t : 'Case ' . $case;
    }

    /** Is the heading of case $case one the exams office set (so it is never replaced by the station number)? */
    public static function hasCustomTitle(array $cfg, int $case): bool
    {
        return trim((string) ($cfg['cases'][$case - 1]['title'] ?? '')) !== '';
    }

    /**
     * The label of question $q (1-based) of case $case. A question the form lists uses its label ("Question N" if
     * left blank); one an examiner added beyond the list uses the old clinical label if there is one, else "Question N".
     */
    public static function questionLabel(array $cfg, int $case, int $q): string
    {
        $list = $cfg['cases'][$case - 1]['questions'] ?? [];
        if ($q <= count($list)) {
            $l = trim((string) $list[$q - 1]);

            return $l !== '' ? $l : 'Question ' . $q;
        }

        return ($cfg['format'] ?? '') === 'clinical' ? (self::CLINICAL_LABELS[$q - 1] ?? 'Question ' . $q) : 'Question ' . $q;
    }

    /** The most questions a case may have when an examiner adds some (4 on Clinical, as always, unless the form lists more). */
    public static function maxQuestions(array $cfg, int $case): int
    {
        $n = count($cfg['cases'][$case - 1]['questions'] ?? []);

        return ($cfg['format'] ?? '') === 'clinical' ? max(4, $n) : 99;
    }

    /**
     * Questions per case for a saved mark row: its case_layout when it matches the marks, else the way the forms
     * always split them (evenly over the old number of cases).
     *
     * @param  mixed  $caseLayout  the case_layout column (JSON string, array or null)
     * @return int[]
     */
    public static function layout(string $exam, string $format, $caseLayout, int $markCount): array
    {
        $saved = is_string($caseLayout) ? json_decode($caseLayout, true) : $caseLayout;
        if (is_array($saved) && $saved && array_sum($saved) === $markCount && min($saved) >= 0) {
            return array_map('intval', array_values($saved));
        }

        $n = max(1, self::legacyCases($exam, $format));
        $per = intdiv($markCount, $n);
        $extra = $markCount - $per * $n;

        return array_map(fn ($i) => $per + ($i < $extra ? 1 : 0), range(0, $n - 1));
    }

    /** Does this results table keep a case layout (the API release with the migration is live)? */
    public static function tableHasLayout(string $table): bool
    {
        static $known = [];

        return $known[$table] ??= (Schema::hasTable($table) && Schema::hasColumn($table, 'case_layout'));
    }

    /** The per-case question counts posted by a marking form (question_marks_case1[], question_marks_case2[]...), in case order. */
    public static function layoutFromRequest(array $input): array
    {
        $counts = [];
        foreach ($input as $key => $value) {
            if (preg_match('/^question_marks_case(\d+)$/', (string) $key, $m) && is_array($value)) {
                $counts[(int) $m[1]] = count($value);
            }
        }
        ksort($counts);

        return array_values($counts);
    }
}
