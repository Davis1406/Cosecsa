<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ExamController extends Controller
{
    private array $programmeMap = [
        'cardiothoracic'         => 1,
        'neurosurgery'           => 3,
        'orthopaedic'            => 4,
        'ent'                    => 5,
        'paediatric_orthopaedics'=> 6,
        'paediatric'             => 7,
        'plastic_surgery'        => 8,
        'urology'                => 9,
    ];

    private array $tableMap = [
        'mcs'                    => 'mcs_results',
        'gs'                     => 'gs_results',
        'cardiothoracic'         => 'cardiothoracic_results',
        'urology'                => 'urology_results',
        'paediatric'             => 'paediatric_results',
        'ent'                    => 'ent_results',
        'orthopaedic'            => 'orthopaedic_results',
        'plastic_surgery'        => 'plastic_surgery_results',
        'neurosurgery'           => 'neurosurgery_results',
        'paediatric_orthopaedics'=> 'paediatric_orthopaedics_results',
    ];

    // ── Candidates ────────────────────────────────────────────────────────────

    public function getCandidates(Request $request, string $examType, int $groupId)
    {
        $yearId  = User::getCurrentYearId();
        $yearName = DB::table('years')->where('id', $yearId)->value('year_name');

        $query = DB::table('candidates')
            ->join('users', 'candidates.user_id', '=', 'users.id')
            ->where('candidates.group_id', $groupId)
            ->where('candidates.exam_year', $yearName)
            ->where('users.is_deleted', 0)
            ->select('candidates.id as candidates_id', 'candidates.candidate_id', 'users.name')
            ->orderBy('candidates.id');

        if (!in_array($examType, ['mcs', 'gs'])) {
            $programmeId = $this->programmeMap[$examType] ?? null;
            if (!$programmeId) {
                return response()->json(['error' => 'Invalid exam type.'], 422);
            }
            $query->where('candidates.programme_id', $programmeId);
        }

        return response()->json($query->get());
    }

    public function getGroups(Request $request)
    {
        $examiner      = DB::table('examiners')->where('user_id', $request->user()->id)->first();
        $currentYearId = User::getCurrentYearId();

        $groups = DB::table('examiners_groups')
            ->join('exams_groups', 'examiners_groups.id', '=', 'exams_groups.group_id')
            ->where('exams_groups.exm_id', $examiner->id)
            ->where('exams_groups.year_id', $currentYearId)
            ->select('examiners_groups.id', 'examiners_groups.group_name')
            ->get();

        return response()->json($groups);
    }

    // ── Single submission ─────────────────────────────────────────────────────

    public function submitMarks(Request $request)
    {
        $examiner = DB::table('examiners')->where('user_id', $request->user()->id)->first();
        if (!$examiner) {
            return response()->json(['error' => 'Examiner not found.'], 403);
        }

        $result = $this->processSubmission((array) $request->all(), $examiner->id);

        if ($result['status'] === 'error') {
            return response()->json(['error' => $result['message']], 422);
        }
        if ($result['status'] === 'duplicate') {
            return response()->json(['error' => $result['message']], 409);
        }

        return response()->json(['message' => 'Marks submitted successfully.']);
    }

    // ── Batch sync (offline queue) ────────────────────────────────────────────

    public function syncBatch(Request $request)
    {
        $examiner = DB::table('examiners')->where('user_id', $request->user()->id)->first();
        if (!$examiner) {
            return response()->json(['error' => 'Examiner not found.'], 403);
        }

        $submissions = $request->input('submissions', []);
        $results     = [];

        foreach ($submissions as $submission) {
            $result = $this->processSubmission($submission, $examiner->id);
            $results[] = [
                'local_id' => $submission['local_id'] ?? null,
                'status'   => $result['status'],
                'message'  => $result['message'] ?? null,
            ];
        }

        return response()->json(['results' => $results]);
    }

    // ── My submitted results ──────────────────────────────────────────────────

    public function getMyResults(Request $request)
    {
        $examiner      = DB::table('examiners')->where('user_id', $request->user()->id)->first();
        $currentYearId = User::getCurrentYearId();
        $results       = [];

        foreach ($this->tableMap as $type => $table) {
            if (!\Schema::hasTable($table)) {
                continue;
            }

            $records = DB::table($table)
                ->join('candidates', "$table.candidate_id", '=', 'candidates.id')
                ->where("$table.examiner_id", $examiner->id)
                ->where("$table.exam_year", $currentYearId)
                ->select("$table.*", 'candidates.candidate_id as candidate_code')
                ->get()
                ->map(fn($r) => array_merge((array) $r, ['exam_type' => $type]));

            $results = array_merge($results, $records->toArray());
        }

        return response()->json($results);
    }

    // ── Shared processing logic ───────────────────────────────────────────────

    private function processSubmission(array $data, int $examinerId): array
    {
        try {
            $examType  = $data['exam_type'] ?? '';
            $tableName = $this->tableMap[$examType] ?? null;

            if (!$tableName) {
                return ['status' => 'error', 'message' => 'Invalid exam type.'];
            }

            $currentYearId = User::getCurrentYearId();

            // Duplicate check
            $dup = DB::table($tableName)
                ->where('candidate_id', $data['candidate_id'])
                ->where('examiner_id', $examinerId)
                ->where('station_id', $data['station_id'])
                ->where('exam_year', $currentYearId);

            if (!in_array($examType, ['mcs', 'gs'])) {
                $dup->where('exam_format', $data['form_type']);
            }

            if ($dup->exists()) {
                return ['status' => 'duplicate', 'message' => 'Marks already submitted for this candidate/station.'];
            }

            // Validate marks
            $questionMarks = $data['question_marks'] ?? [];
            $isSpecialViva = in_array($examType, ['urology', 'paediatric']) && ($data['form_type'] ?? '') === 'viva';

            foreach ($questionMarks as $mark) {
                if ($mark === null || $mark === '') {
                    continue;
                }
                $allowed = $isSpecialViva ? [0, 2, 4, 6, 8, 10] : [2, 4, 6, 8, 10];
                if (!in_array((int) $mark, $allowed)) {
                    return ['status' => 'error', 'message' => 'Invalid mark value: ' . $mark];
                }
            }

            $row = [
                'candidate_id' => $data['candidate_id'],
                'examiner_id'  => $examinerId,
                'station_id'   => $data['station_id'],
                'group_id'     => $data['group_id'],
                'question_mark'=> json_encode(array_map('intval', array_filter($questionMarks, fn($m) => $m !== null && $m !== ''))),
                'total'        => $data['total_marks'],
                'remarks'      => $data['remarks'] ?? null,
                'exam_year'    => $currentYearId,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];

            if ($examType === 'mcs') {
                $row['overall'] = strtolower($data['overall'] ?? '');
            }
            if (!in_array($examType, ['mcs', 'gs'])) {
                $row['exam_format'] = $data['form_type'];
            }

            DB::table($tableName)->insert($row);

            return ['status' => 'success'];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
