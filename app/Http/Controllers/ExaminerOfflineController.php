<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Support endpoints for offline marking on the examiner pages.
 *
 * The browser (public/dist/js/examiner-offline.js + public/examiner-sw.js)
 * caches the marking pages and candidate lists, queues marks entered without
 * a connection, and posts them to the normal marking endpoints once back
 * online. This controller only tells the browser *what* to cache and gives it
 * a fresh CSRF token to sync with (a queued mark can outlive the token baked
 * into the page it was entered on).
 */
class ExaminerOfflineController extends Controller
{
    /** exam type => programme id, for the FCS specialties served by the universal form. */
    private const FCS_PROGRAMMES = [
        'cardiothoracic'          => 1,
        'neurosurgery'            => 3,
        'orthopaedic'             => 4,
        'ent'                     => 5,
        'paediatric_orthopaedics' => 6,
        'paediatric'              => 7,
        'plastic_surgery'         => 8,
        'urology'                 => 9,
    ];

    /** Results tables with a Clinical/Viva split; one Resubmit flow per candidate. */
    private const FCS_RESULT_TABLES = [
        'cardiothoracic_results', 'urology_results', 'paediatric_results', 'ent_results',
        'plastic_surgery_results', 'neurosurgery_results', 'paediatric_orthopaedics_results', 'orthopaedic_results',
    ];

    private const GS_PROGRAMME  = 2;
    private const MCS_PROGRAMME = 10;

    public function token(): JsonResponse
    {
        return $this->noStore(response()->json([
            'token'   => csrf_token(),
            'user_id' => Auth::id(),
        ]));
    }

    public function manifest(): JsonResponse
    {
        return $this->noStore(response()->json([
            'user_id'      => Auth::id(),
            'generated_at' => now()->toIso8601String(),
            'pages'        => $this->pageUrls(),
            'data'         => $this->candidateUrls(),
        ]));
    }

    /** Every examiner page that is needed to mark a candidate. */
    private function pageUrls(): array
    {
        $paths = ['examiner/dashboard', 'examiner/examiner_form', 'examiner/general_surgery', 'examiner/results'];

        foreach (array_keys(self::FCS_PROGRAMMES) as $type) {
            $paths[] = "examiner/{$type}";
            $paths[] = "examiner/{$type}/clinical";
            $paths[] = "examiner/{$type}/viva";
        }

        $paths[] = 'examiner/profile_settings';
        $paths = array_merge($paths, $this->profilePaths(), $this->resultPaths());

        return array_values(array_unique(array_map(fn ($path) => url($path), $paths)));
    }

    /** The examiner's own profile pages (viewable offline; saving needs a connection). */
    private function profilePaths(): array
    {
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');

        return $examinerId ? ["examiner/edit_info/{$examinerId}"] : [];
    }

    /**
     * View / Resubmit pages for each result this examiner has already saved
     * this year, so a mark can be reviewed and corrected without a connection.
     * Sandbox (test_) marks have no such pages and are left out.
     */
    private function resultPaths(): array
    {
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');
        $yearId = User::getCurrentYearId();
        if (! $examinerId || ! $yearId) {
            return [];
        }

        $paths = [];

        foreach (['mcs_results', 'gs_results'] as $table) {
            if (! Schema::hasTable($table)) continue;

            $rows = DB::table($table)->where('examiner_id', $examinerId)->where('exam_year', $yearId)
                ->select('candidate_id', 'station_id')->distinct()->get();
            foreach ($rows as $row) {
                $paths[] = "examiner/resubmit/{$row->candidate_id}/{$row->station_id}";
                $paths[] = "examiner/view_results/{$row->candidate_id}/{$row->station_id}";
            }
        }

        foreach (self::FCS_RESULT_TABLES as $table) {
            if (! Schema::hasTable($table)) continue;

            $rows = DB::table($table)->where('examiner_id', $examinerId)->where('exam_year', $yearId)
                ->select('candidate_id', 'exam_format')->distinct()->get();
            foreach ($rows as $row) {
                $paths[] = "examiner/fcs-resubmit/{$row->candidate_id}";
                $paths[] = "examiner/view_fcs_results/{$row->candidate_id}";
                if (in_array($row->exam_format, ['clinical', 'viva'], true)) {
                    $paths[] = "examiner/fcs-resubmit/{$row->candidate_id}/{$row->exam_format}";
                }
            }
        }

        return $paths;
    }

    /**
     * The candidate lists the forms fetch after a group is picked: one URL per
     * (group, programme) that has candidates in the current exam year.
     */
    private function candidateUrls(): array
    {
        $yearName = DB::table('years')->where('id', User::getCurrentYearId())->value('year_name');
        if (! $yearName) {
            return [];
        }

        $pairs = DB::table('candidates')
            ->whereNotNull('group_id')
            ->where('exam_year', $yearName)
            ->select('group_id', 'programme_id')
            ->distinct()
            ->orderBy('group_id')
            ->get();

        $typeByProgramme = array_flip(self::FCS_PROGRAMMES);
        $urls = [];

        foreach ($pairs as $pair) {
            $group = (int) $pair->group_id;
            $programme = (int) $pair->programme_id;

            if ($programme === self::MCS_PROGRAMME) {
                $urls[] = url("get-mcs-candidates/{$group}");
            } elseif ($programme === self::GS_PROGRAMME) {
                $urls[] = url("get-gs-candidates/{$group}");
            } elseif (isset($typeByProgramme[$programme])) {
                $urls[] = url("get-exam-candidates/{$typeByProgramme[$programme]}/{$group}");
            }
        }

        return array_values(array_unique($urls));
    }

    /** These responses must never be served from a cache. */
    private function noStore(JsonResponse $response): JsonResponse
    {
        return $response->header('Cache-Control', 'no-store, private');
    }
}
