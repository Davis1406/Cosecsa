<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use App\Models\User;
use App\Models\Trainee;
use App\Models\Candidates;
use App\Models\CandidatesFormModel;
use App\Models\GeneralSurgery;
use App\Models\HospitalModel;
use App\Models\Programme;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CandidatesController extends Controller
{
    public function __construct(private ApiClient $api) {}

    // ── Admin CRUD (proxied via API) ──────────────────────────────────────────

    public function reports()
    {
        $response = $this->api->get('candidates/reports/filters');
        $data = $response->object();

        return view('admin.associates.candidates.reports', [
            'header_title'    => 'Candidates Analytics',
            'filterCountries' => collect($data->filterCountries ?? []),
            'filterProgrammes'=> collect($data->filterProgrammes ?? []),
            'filterYears'     => collect($data->filterYears ?? []),
        ]);
    }

    public function reportsData()
    {
        $response = $this->api->get('candidates/reports/data', request()->all());
        return response()->json($response->json());
    }

    public function list()
    {
        $response = $this->api->get('candidates/list-data');
        $data = $response->object();

        return view('admin.associates.candidates.list', [
            'getRecord'       => collect($data->candidates ?? []),
            'header_title'    => 'Candidates List',
            'filterCountries' => collect($data->filterCountries ?? []),
            'filterProgrammes'=> collect($data->filterProgrammes ?? []),
            'filterYears'     => collect($data->filterYears ?? []),
        ]);
    }

    public function view($id)
    {
        $response = $this->api->get("candidates/{$id}/detail");
        if ($response->status() === 404) {
            return redirect('admin/associates/candidates/list')->with('error', 'Candidate not found');
        }
        $data = $response->object();

        return view('admin.associates.candidates.view_candidate', [
            'candidate'     => $data->candidate,
            'header_title'  => 'View Candidate',
            'linkedTrainee' => $data->linkedTrainee ?? null,
        ]);
    }

    public function add()
    {
        return view('admin.associates.candidates.add', [
            'getHospital'  => HospitalModel::getHospital(),
            'getProgramme' => Programme::getProgramme(),
            'getCountry'   => Country::getCountry(),
            'header_title' => 'Add New Candidate',
        ]);
    }

    public function import()
    {
        return view('admin.associates.candidates.import', [
            'header_title' => 'Import Candidates',
        ]);
    }

    public function importData(Request $request)
    {
        set_time_limit(300);

        $request->validate(['file' => 'required|mimes:csv,xlsx,xls|max:2048']);

        $this->api->postWithFile('candidates/import', [], ['file' => $request->file('file')]);

        return redirect('admin/associates/candidates/list')->with('success', 'Candidates imported successfully');
    }

    public function insert(Request $request)
    {
        $fields = $request->only([
            'firstname', 'middlename', 'lastname', 'email', 'password', 'personal_email',
            'gender', 'programme_id', 'hospital_id', 'country_id', 'entry_number',
            'exam_number', 'repeat_paper_one', 'repeat_paper_two', 'mmed',
            'admission_year', 'exam_year', 'sponsor', 'remarks',
            'invoice_number', 'invoice_date', 'invoice_amount', 'invoice_status',
            'fee_paid', 'amount_paid', 'payment_date', 'mode_of_payment',
        ]);

        $this->api->post('candidates/', $fields);

        return redirect('admin/associates/candidates/list')->with('success', 'Candidate added successfully.');
    }

    public function edit($id)
    {
        $response = $this->api->get("candidates/{$id}/detail");
        if ($response->status() === 404) {
            return redirect('admin/associates/candidates/list')->with('error', 'Candidate not found');
        }
        $data = $response->object();

        return view('admin.associates.candidates.edit_candidate', [
            'candidate'    => $data->candidate,
            'getHospital'  => HospitalModel::getHospital(),
            'getProgramme' => Programme::getProgramme(),
            'getCountry'   => Country::getCountry(),
            'header_title' => 'Edit Candidate',
        ]);
    }

    public function update(Request $request, $id)
    {
        $fields = $request->only([
            'firstname', 'middlename', 'lastname', 'email', 'password', 'personal_email',
            'gender', 'programme_id', 'hospital_id', 'country_id', 'entry_number',
            'candidate_id', 'exam_number', 'repeat_paper_one', 'repeat_paper_two',
            'admission_year', 'exam_year', 'mmed', 'invoice_number', 'invoice_date',
            'invoice_status', 'invoice_amount', 'sponsor', 'fee_paid',
            'amount_paid', 'payment_date', 'mode_of_payment', 'remarks',
        ]);

        $this->api->post("candidates/{$id}", $fields);

        return redirect('admin/associates/candidates/list')->with('success', 'Candidate updated successfully');
    }

    public function delete($id)
    {
        $this->api->delete("candidates/{$id}");

        return redirect('admin/associates/candidates/list')->with('success', 'Candidate deleted successfully');
    }

    // ── Examiner-facing exam-marking methods (direct DB — excluded from proxy) ─

    public function examinerList()
    {
        $data['getRecord'] = User::getexaminerCandidates();
        $data['header_title'] = "Candidates List";
        return view('examiner.candidates_list', $data);
    }

    public function mcsexaminerform()
    {
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');
        $currentYearId = User::getCurrentYearId();
        $examinerGroupIds = DB::table('exams_groups')
            ->where('exm_id', $examinerId)->where('year_id', $currentYearId)
            ->pluck('group_id')->toArray();
        $groups = DB::table('examiners_groups')->get();

        $data['header_title'] = 'MCS Form';
        $data['getRecord'] = User::getExaminerCandidates(null, $currentYearId);
        $data['groups'] = $groups;
        $data['examinerGroupIds'] = $examinerGroupIds;
        return view('examiner.examiner_form', $data);
    }

    public function gsexaminerform()
    {
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');
        $currentYearId = User::getCurrentYearId();
        $examinerGroupIds = DB::table('exams_groups')
            ->where('exm_id', $examinerId)->where('year_id', $currentYearId)
            ->pluck('group_id')->toArray();
        $groups = DB::table('examiners_groups')->get();

        $data['header_title'] = 'GS Form';
        $data['getRecord'] = User::getExaminerCandidates(null, $currentYearId);
        $data['groups'] = $groups;
        $data['examinerGroupIds'] = $examinerGroupIds;
        return view('examiner.general_surgery', $data);
    }

    public function getMcsCandidatesByGroup($groupId)
    {
        $currentYearId = User::getCurrentYearId();
        $currentYear = DB::table('years')->where('id', $currentYearId)->value('year_name');
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');

        if (!$examinerId) {
            return response()->json(['error' => 'Examiner not found']);
        }

        $candidates = DB::table('candidates')
            ->join('users', 'candidates.user_id', '=', 'users.id')
            ->where('candidates.programme_id', 10)
            ->where('candidates.group_id', $groupId)
            ->where('candidates.exam_year', $currentYear)
            ->where('users.is_deleted', '0')
            ->select('candidates.id as candidates_id', 'candidates.candidate_id', 'users.name', 'candidates.exam_year', 'candidates.group_id')
            ->orderBy('candidates.id', 'asc')
            ->get();

        return response()->json($candidates);
    }

    public function getGsCandidatesByGroup($groupId)
    {
        $currentYearId = User::getCurrentYearId();
        $currentYear = DB::table('years')->where('id', $currentYearId)->value('year_name');
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');

        if (!$examinerId) {
            return response()->json(['error' => 'Examiner not found']);
        }

        $candidates = DB::table('candidates')
            ->join('users', 'candidates.user_id', '=', 'users.id')
            ->where('candidates.programme_id', 2)
            ->where('candidates.exam_year', $currentYear)
            ->where('users.is_deleted', '0')
            ->select('candidates.id as candidates_id', 'candidates.candidate_id', 'users.name', 'candidates.exam_year')
            ->orderBy('candidates.id', 'asc')
            ->get();

        return response()->json($candidates);
    }

    public function storeEvaluation(Request $request)
    {
        $examiner = DB::table('examiners')->where('user_id', Auth::id())->first();
        if (!$examiner) return back()->with('error', 'Examiner data not found.');

        $currentYearId = User::getCurrentYearId();

        $existingSubmission = DB::table('mcs_results')
            ->where('candidate_id', $request->candidate_id)
            ->where('examiner_id', $examiner->id)
            ->where('station_id', $request->station_id)
            ->where('exam_year', $currentYearId)
            ->first();

        if ($existingSubmission) {
            return back()->with('error', 'Candidate marks already submitted for this station. Please use Resubmit to edit.');
        }

        $evaluation = new CandidatesFormModel();
        $evaluation->candidate_id = $request->candidate_id;
        $evaluation->examiner_id = $examiner->id;
        $evaluation->station_id = $request->station_id;
        $evaluation->group_id = $request->group_id;
        $evaluation->question_mark = json_encode($request->question_marks);
        $evaluation->total = $request->total_marks;
        $evaluation->overall = strtolower($request->overall);
        $evaluation->remarks = $request->remarks;
        $evaluation->exam_year = $currentYearId;
        $evaluation->save();

        return redirect()->back()->with('success', 'Evaluation submitted successfully.');
    }

    public function storegsEvaluation(Request $request)
    {
        $examiner = DB::table('examiners')->where('user_id', Auth::id())->first();
        if (!$examiner) {
            return back()->with('error', 'Examiner data not found.');
        }

        $currentYearId = User::getCurrentYearId();

        $existingSubmission = DB::table('gs_results')
            ->where('candidate_id', $request->candidate_id)
            ->where('examiner_id', $examiner->id)
            ->where('station_id', $request->station_id)
            ->where('exam_year', $currentYearId)
            ->first();

        if ($existingSubmission) {
            return back()->with('error', 'Candidate marks already submitted for this station. Please use Resubmit to edit.');
        }

        $evaluation = new GeneralSurgery();
        $evaluation->candidate_id = $request->candidate_id;
        $evaluation->examiner_id = $examiner->id;
        $evaluation->station_id = $request->station_id;
        $evaluation->group_id = $request->group_id;
        $evaluation->question_mark = json_encode($request->question_marks);
        $evaluation->total = $request->total_marks;
        $evaluation->remarks = $request->remarks;
        $evaluation->exam_year = $currentYearId;
        $evaluation->save();

        return redirect()->back()->with('success', 'GS Evaluation submitted successfully.');
    }

    public function results()
    {
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');
        if (!$examinerId) {
            return back()->with('error', 'Examiner data not found.');
        }

        $currentExamYearId = User::getCurrentYearId();
        $mcsTable = 'mcs_results';
        $gsTable = 'gs_results';
        $fcsGroupedTables = [
            'cardiothoracic_results', 'orthopaedic_results', 'ent_results',
            'urology_results', 'neurosurgery_results', 'paediatric_orthopaedics_results',
            'paediatric_results', 'plastic_surgery_results',
        ];

        $mcsResults = collect();
        $gsResults = collect();
        $fcsResults = collect();
        $lastMcsTimestamp = $lastGsTimestamp = $lastFcsTimestamp = $lastFcsTable = null;

        if (\Schema::hasTable($mcsTable)) {
            $latestMcs = DB::table($mcsTable)->where('examiner_id', $examinerId)->where('exam_year', $currentExamYearId)->latest('created_at')->first();
            if ($latestMcs) $lastMcsTimestamp = $latestMcs->created_at;
            $mcsResults = DB::table($mcsTable)
                ->select("{$mcsTable}.*", "{$mcsTable}.id as record_id", 'candidates.candidate_id as candidate_name', 'examiners_groups.group_name')
                ->join('candidates', "{$mcsTable}.candidate_id", '=', 'candidates.id')
                ->join('examiners_groups', "{$mcsTable}.group_id", '=', 'examiners_groups.id')
                ->where("{$mcsTable}.examiner_id", $examinerId)->where("{$mcsTable}.exam_year", $currentExamYearId)->get();
        }

        if (\Schema::hasTable($gsTable)) {
            $latestGs = DB::table($gsTable)->where('examiner_id', $examinerId)->where('exam_year', $currentExamYearId)->latest('created_at')->first();
            if ($latestGs) $lastGsTimestamp = $latestGs->created_at;
            $gsResults = DB::table($gsTable)
                ->select("{$gsTable}.*", "{$gsTable}.id as record_id", 'candidates.candidate_id as candidate_name', 'examiners_groups.group_name')
                ->join('candidates', "{$gsTable}.candidate_id", '=', 'candidates.id')
                ->join('examiners_groups', "{$gsTable}.group_id", '=', 'examiners_groups.id')
                ->where("{$gsTable}.examiner_id", $examinerId)->where("{$gsTable}.exam_year", $currentExamYearId)->get();
        }

        foreach ($fcsGroupedTables as $table) {
            if (!\Schema::hasTable($table)) continue;
            $latest = DB::table($table)->where('examiner_id', $examinerId)->where('exam_year', $currentExamYearId)->latest('created_at')->first();
            if ($latest && (!$lastFcsTimestamp || $latest->created_at > $lastFcsTimestamp)) {
                $lastFcsTimestamp = $latest->created_at;
                $lastFcsTable = $table;
            }
            $records = DB::table($table)
                ->select("{$table}.*", "{$table}.id as record_id", 'candidates.candidate_id as candidate_name', 'examiners_groups.group_name', DB::raw("'$table' as source_table"))
                ->join('candidates', "{$table}.candidate_id", '=', 'candidates.id')
                ->join('examiners_groups', "{$table}.group_id", '=', 'examiners_groups.id')
                ->where("{$table}.examiner_id", $examinerId)->where("{$table}.exam_year", $currentExamYearId)->get();
            $fcsResults = $fcsResults->merge($records);
        }

        $showMcs = !$mcsResults->isEmpty();
        $showGs = !$gsResults->isEmpty();
        $showFcs = !$fcsResults->isEmpty();

        $mostRecentType = collect([
            ['type' => 'mcs', 'timestamp' => $lastMcsTimestamp],
            ['type' => 'gs',  'timestamp' => $lastGsTimestamp],
            ['type' => 'fcs', 'timestamp' => $lastFcsTimestamp],
        ])->filter(fn($i) => $i['timestamp'] !== null)->sortByDesc('timestamp')->first()['type'] ?? null;

        $fcsGroupedResults = collect();
        if ($showFcs) {
            $fcsGroupedResults = $fcsResults->groupBy(fn($row) => $row->candidate_id . '-' . $row->source_table)
                ->map(function ($records) {
                    $clinical = $records->where('exam_format', 'clinical');
                    $viva = $records->where('exam_format', 'viva');
                    return (object)[
                        'candidate_id'   => $records->first()->candidate_id,
                        'candidate_name' => $records->first()->candidate_name,
                        'group_name'     => $records->first()->group_name,
                        'station_id'     => $records->first()->station_id ?? 0,
                        'records'        => $records,
                        'clinical_total' => $clinical->sum('total'),
                        'viva_total'     => $viva->sum('total'),
                        'overall_total'  => $records->sum('total'),
                        'formats'        => $records->groupBy('exam_format'),
                        'source_table'   => $records->first()->source_table,
                    ];
                })->values();
        }

        if ($mostRecentType === 'mcs') {
            return view('examiner.results', [
                'mcsResults' => $mcsResults, 'gsResults' => collect(), 'fcsResults' => collect(),
                'showMcs' => true, 'showGs' => false, 'showFcs' => false,
                'lastSubmittedForm' => $mcsTable, 'header_title' => 'Results Summary',
            ]);
        }

        return view('examiner.results', [
            'mcsResults' => collect(), 'gsResults' => $gsResults, 'fcsResults' => $fcsGroupedResults,
            'showMcs' => false, 'showGs' => $showGs, 'showFcs' => $showFcs,
            'lastSubmittedForm' => $mostRecentType === 'gs' ? $gsTable : $lastFcsTable,
            'header_title' => 'Results Summary',
        ]);
    }

    public function viewFcsResults($candidate_id)
    {
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');
        if (!$examinerId) return back()->with('error', 'Examiner data not found.');

        $currentExamYearId = User::getCurrentYearId();
        $fcsTablesWithFormat = [
            'cardiothoracic_results', 'urology_results', 'paediatric_results', 'ent_results',
            'plastic_surgery_results', 'neurosurgery_results', 'paediatric_orthopaedics_results', 'fcs_results',
        ];

        $candidateRecords = collect();
        $sourceTable = null;

        foreach ($fcsTablesWithFormat as $table) {
            if (!\Schema::hasTable($table)) continue;
            $records = DB::table($table)
                ->select("{$table}.*", 'candidates.id as candidate_id', 'candidates.candidate_id as candidate_name', 'examiners.id as examiner_id', 'examiners_groups.group_name', DB::raw("'$table' as source_table"))
                ->join('candidates', "{$table}.candidate_id", '=', 'candidates.id')
                ->join('examiners', "{$table}.examiner_id", '=', 'examiners.id')
                ->join('examiners_groups', "{$table}.group_id", '=', 'examiners_groups.id')
                ->where('candidates.id', $candidate_id)->where('examiners.id', $examinerId)->where("{$table}.exam_year", $currentExamYearId)->get();
            if ($records->isNotEmpty()) { $candidateRecords = $candidateRecords->merge($records); $sourceTable = $table; }
        }

        if ($candidateRecords->isEmpty()) return back()->with('error', 'No results found for this candidate.');

        $clinicalRecords = $candidateRecords->where('exam_format', 'clinical');
        $vivaRecords = $candidateRecords->where('exam_format', 'viva');
        $candidateResult = (object)[
            'candidate_id' => $candidateRecords->first()->candidate_id,
            'candidate_name' => $candidateRecords->first()->candidate_name,
            'group_name' => $candidateRecords->first()->group_name,
            'remarks' => $candidateRecords->first()->remarks,
            'clinical_total' => $clinicalRecords->sum('total'),
            'viva_total' => $vivaRecords->sum('total'),
            'overall_total' => $candidateRecords->sum('total'),
            'clinical_records' => $clinicalRecords,
            'viva_records' => $vivaRecords,
        ];

        return view('examiner.view_fcs_results', ['candidateResult' => $candidateResult, 'header_title' => "Candidate Full Results", 'lastSource' => $sourceTable]);
    }

    public function viewCandidateResults($candidate_id, $station_id)
    {
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');
        if (!$examinerId) return back()->with('error', 'Examiner data not found.');

        $currentExamYearId = User::getCurrentYearId();
        $questionTables = ['mcs_results', 'gs_results'];
        $candidateRecords = collect();
        $sourceTable = null;

        foreach ($questionTables as $table) {
            if (!\Schema::hasTable($table)) continue;
            $records = DB::table($table)
                ->select("{$table}.*", 'candidates.id as candidate_id', 'candidates.candidate_id as candidate_name', 'examiners.id as examiner_id', 'examiners_groups.group_name', DB::raw("'$table' as source_table"))
                ->join('candidates', "{$table}.candidate_id", '=', 'candidates.id')
                ->join('examiners', "{$table}.examiner_id", '=', 'examiners.id')
                ->join('examiners_groups', "{$table}.group_id", '=', 'examiners_groups.id')
                ->where('candidates.id', $candidate_id)->where('examiners.id', $examinerId)
                ->where("{$table}.station_id", $station_id)->where("{$table}.exam_year", $currentExamYearId)->first();
            if ($records) { $candidateRecords->push($records); $sourceTable = $table; break; }
        }

        if ($candidateRecords->isEmpty()) return back()->with('error', 'No results found for this candidate.');

        $firstRecord = $candidateRecords->first();
        $candidateResult = (object)[
            'candidate_id'   => $firstRecord->candidate_id,
            'candidate_name' => $firstRecord->candidate_name,
            'group_name'     => $firstRecord->group_name,
            'station_id'     => $firstRecord->station_id,
            'question_mark'  => $firstRecord->question_mark,
            'total'          => $firstRecord->total,
            'overall'        => $firstRecord->overall ?? null,
            'remarks'        => $firstRecord->remarks ?? '',
            'source_table'   => $firstRecord->source_table,
        ];

        return view('examiner.view_results', ['candidateResult' => $candidateResult, 'header_title' => "Candidate Results", 'lastSource' => $sourceTable]);
    }

    public function resubmit($candidate_id, $station_id)
    {
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');
        if (!$examinerId) return redirect()->back()->with('error', 'Examiner data not found.');

        $currentExamYearId = User::getCurrentYearId();
        $allTables = array_merge(['mcs_results', 'gs_results'], [
            'cardiothoracic_results', 'urology_results', 'paediatric_results', 'ent_results',
            'plastic_surgery_results', 'neurosurgery_results', 'paediatric_orthopaedics_results', 'orthopaedic_results', 'fcs_results',
        ]);

        $candidateRecords = collect();
        $sourceTable = null;

        foreach ($allTables as $table) {
            if (!\Schema::hasTable($table)) continue;
            $records = DB::table($table)
                ->select("{$table}.*", 'candidates.id as candidates_id', 'candidates.candidate_id as candidate_name', 'examiners.id as examiner_id', 'examiners_groups.id as group_table_id', 'examiners_groups.group_name', DB::raw("'$table' as source_table"))
                ->join('candidates', "{$table}.candidate_id", '=', 'candidates.id')
                ->join('examiners', "{$table}.examiner_id", '=', 'examiners.id')
                ->join('examiners_groups', "{$table}.group_id", '=', 'examiners_groups.id')
                ->where('candidates.id', $candidate_id)->where('examiners.id', $examinerId)
                ->where("{$table}.station_id", $station_id)->where("{$table}.exam_year", $currentExamYearId)->get();
            if ($records->isNotEmpty()) { $candidateRecords = $candidateRecords->merge($records); $sourceTable = $table; break; }
        }

        if ($candidateRecords->isEmpty()) return redirect()->back()->with('error', 'Candidate evaluation not found.');

        $candidate = $candidateRecords->first();
        if (isset($candidate->group_name)) $candidate->g_id = trim(str_replace(['Group', 'group'], '', $candidate->group_name));
        if (isset($candidate->question_mark)) $candidate->question_mark = json_decode($candidate->question_mark, true);

        $fcsTablesWithFormat = ['cardiothoracic_results', 'urology_results', 'paediatric_results', 'ent_results', 'plastic_surgery_results', 'neurosurgery_results', 'paediatric_orthopaedics_results', 'orthopaedic_results', 'fcs_results'];

        if ($sourceTable === 'gs_results') return view('examiner.gsresubmit', ['candidate' => $candidate, 'header_title' => 'Resubmit GS Evaluation']);
        if ($sourceTable === 'mcs_results') return view('examiner.resubmit', ['candidate' => $candidate, 'header_title' => 'Resubmit MCS Evaluation']);
        if (in_array($sourceTable, $fcsTablesWithFormat)) {
            $fullCandidate = DB::table('candidates')->where('id', $candidate_id)->first();
            $exam_name = DB::table('programmes')->where('id', $fullCandidate->programme_id)->value('programme_name') ?? 'Unknown Exam';
            return view('examiner.fcs_resubmit_selection', ['candidate' => $fullCandidate, 'exam_name' => $exam_name, 'header_title' => 'Resubmit Clinical + Viva']);
        }

        return redirect()->back()->with('error', 'Unknown exam type.');
    }

    public function updateEvaluation(Request $request, $candidate_id, $station_id)
    {
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');
        if (!$examinerId) return redirect()->back()->with('error', 'Examiner data not found.');

        $tables = [
            'mcs_results' => ['grade_field' => 'overall'], 'gs_results' => ['grade_field' => null],
            'cardiothoracic_results' => ['grade_field' => 'grade'], 'ent_results' => ['grade_field' => 'grade'],
            'urology_results' => ['grade_field' => 'grade'], 'neurosurgery_results' => ['grade_field' => 'grade'],
            'plastics_results' => ['grade_field' => 'grade'], 'paediatrics_results' => ['grade_field' => 'grade'],
            'orthopaedic_results' => ['grade_field' => 'grade'],
        ];

        $evaluation = $sourceTable = null;
        foreach ($tables as $table => $settings) {
            if (!\Schema::hasTable($table)) continue;
            $record = DB::table($table)->where('candidate_id', $candidate_id)->where('station_id', $station_id)->where('examiner_id', $examinerId)->first();
            if ($record) { $evaluation = $record; $sourceTable = $table; break; }
        }

        if (!$evaluation) return redirect()->back()->with('error', 'Evaluation not found.');

        $updateData = ['group_id' => $request->group_id, 'station_id' => $request->station_id, 'question_mark' => json_encode($request->question_marks ?? []), 'total' => $request->total_marks, 'remarks' => $request->remarks];
        $gradeField = $tables[$sourceTable]['grade_field'] ?? null;
        if ($gradeField) $updateData[$gradeField] = $request->grade;

        DB::table($sourceTable)->where('candidate_id', $candidate_id)->where('station_id', $station_id)->where('examiner_id', $examinerId)->update($updateData);

        return redirect('examiner/results')->with('success', 'Evaluation updated successfully.');
    }

    public function showFcsResubmitSelection($candidate_id)
    {
        $candidate = DB::table('candidates')->where('id', $candidate_id)->first();
        if (!$candidate) return redirect()->back()->with('error', 'Candidate not found.');
        $programmeName = DB::table('programmes')->where('id', $candidate->programme_id)->value('name') ?? 'Unknown Exam';
        return view('examiner.fcs_resubmit_selection', ['candidate' => $candidate, 'exam_name' => $programmeName, 'header_title' => 'Resubmit Evaluation']);
    }

    public function showFcsResubmitForm($candidate_id, $exam_format)
    {
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');
        if (!$examinerId) return redirect()->back()->with('error', 'Examiner data not found.');

        $currentYearId = User::getCurrentYearId();
        $examinerGroupIds = DB::table('exams_groups')->where('exm_id', $examinerId)->where('year_id', $currentYearId)->pluck('group_id')->toArray();
        $groups = DB::table('examiners_groups')->get();
        $candidate = DB::table('candidates')->where('id', $candidate_id)->first();
        if (!$candidate) return redirect()->back()->with('error', 'Candidate not found.');

        $programmeToExamType = [1=>'cardiothoracic',3=>'neurosurgery',4=>'orthopaedic',5=>'ent',6=>'paediatric_orthopaedics',7=>'paediatric',8=>'plastic_surgery',9=>'urology',2=>'gs',10=>'mcs'];
        $exam_type = $programmeToExamType[$candidate->programme_id] ?? 'unknown';

        $fcsTablesWithFormat = ['cardiothoracic_results','urology_results','paediatric_results','ent_results','plastic_surgery_results','neurosurgery_results','paediatric_orthopaedics_results','orthopaedic_results','fcs_results'];

        $existingRecord = $foundTable = null;
        foreach ($fcsTablesWithFormat as $table) {
            if (!\Schema::hasTable($table)) continue;
            $record = DB::table($table)->where('candidate_id', $candidate_id)->where('examiner_id', $examinerId)->where('exam_year', $currentYearId)->where('exam_format', $exam_format)->first();
            if ($record) { $existingRecord = $record; $foundTable = $table; break; }
        }

        if (!$existingRecord) return redirect()->back()->with('error', 'No existing evaluation found for this exam format.');

        $casesCount = 2;
        if ($exam_format === 'clinical') {
            $casesCount = in_array($exam_type, ['plastic_surgery', 'neurosurgery']) ? 2 : 1;
        } elseif ($exam_type === 'cardiothoracic' && $exam_format === 'viva') {
            $casesCount = 4;
        }

        $candidateData = (object)[
            'candidates_id' => $candidate->id,
            'candidate_id'  => $candidate->candidate_id,
            'candidate_name'=> DB::table('users')->where('id', $candidate->user_id)->value('name'),
            'g_id'          => $existingRecord->group_id,
            'g_name'        => DB::table('examiners_groups')->where('id', $existingRecord->group_id)->value('group_name'),
            'station_id'    => $existingRecord->station_id,
            'question_mark' => json_decode($existingRecord->question_mark, true),
            'total'         => $existingRecord->total,
            'remarks'       => $existingRecord->remarks,
            'grade'         => $existingRecord->grade ?? ($existingRecord->overall ?? null),
        ];

        if (in_array($candidate->programme_id, [1,3,4,5,6,7,8,9])) {
            $examNames = ['cardiothoracic'=>'Cardiothoracic Surgery','urology'=>'Urology','orthopaedic'=>'Orthopaedic','paediatric'=>'Paediatric Surgery','ent'=>'ENT Surgery','plastic_surgery'=>'Plastic Surgery','neurosurgery'=>'Neurosurgery','paediatric_orthopaedics'=>'Paediatric Orthopaedics'];
            return view('examiner.resubmit_form_universal', ['candidate' => $candidateData, 'exam_name' => DB::table('programmes')->where('id', $candidate->programme_id)->value('name'), 'exam_type' => $exam_type, 'form_type' => $exam_format, 'cases_count' => $casesCount, 'programme_id' => $candidate->programme_id, 'groups' => $groups, 'examinerGroupIds' => $examinerGroupIds, 'header_title' => 'Resubmit '.ucfirst($exam_format), 'isResubmit' => true]);
        }

        return view('examiner.resubmit', ['candidate' => $candidateData, 'header_title' => 'Resubmit Evaluation', 'isResubmit' => true]);
    }

    public function updateEvaluationFcs(Request $request, $candidate_id)
    {
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');
        if (!$examinerId) return redirect()->back()->with('error', 'Examiner data not found.');

        $currentYearId = User::getCurrentYearId();
        $fcsTablesWithFormat = ['cardiothoracic_results'=>['grade_field'=>'grade'],'urology_results'=>['grade_field'=>'grade'],'paediatric_results'=>['grade_field'=>'grade'],'ent_results'=>['grade_field'=>'grade'],'plastic_surgery_results'=>['grade_field'=>'grade'],'neurosurgery_results'=>['grade_field'=>'grade'],'paediatric_orthopaedics_results'=>['grade_field'=>'grade'],'orthopaedic_results'=>['grade_field'=>'grade'],'fcs_results'=>['grade_field'=>'grade']];

        $questionMarks = $request->question_marks ?? [];
        if (empty($questionMarks)) return redirect()->back()->with('error', 'No question marks provided.');

        $updated = false;
        foreach ($fcsTablesWithFormat as $table => $settings) {
            if (!\Schema::hasTable($table)) continue;
            $record = DB::table($table)->where('candidate_id', $candidate_id)->where('examiner_id', $examinerId)->where('exam_format', $request->exam_format ?? $request->form_type)->where('exam_year', $currentYearId)->first();
            if (!$record) continue;
            $updateData = ['group_id' => $request->group_id ?? $record->group_id, 'station_id' => $request->station_id ?? $record->station_id, 'question_mark' => json_encode($questionMarks), 'total' => $request->total_marks ?? array_sum($questionMarks), 'remarks' => $request->remarks ?? $record->remarks, 'updated_at' => now()];
            if ($settings['grade_field'] && isset($request->grade)) $updateData[$settings['grade_field']] = $request->grade;
            DB::table($table)->where('id', $record->id)->update($updateData);
            $updated = true;
            break;
        }

        if (!$updated) return redirect()->back()->with('error', 'No evaluation found to update.');
        return redirect('examiner/results')->with('success', 'Evaluation updated successfully.');
    }

    public function cardiothoracicSelection() { return view('examiner.exam_type_selection', ['header_title'=>'FCS Cardiothoracic - Select Exam Type','exam_name'=>'Cardiothoracic Surgery','exam_type'=>'cardiothoracic']); }
    public function urologySelection() { return view('examiner.exam_type_selection', ['header_title'=>'FCS Urology - Select Exam Type','exam_name'=>'Urology','exam_type'=>'urology']); }
    public function orthopaedicSelection() { return view('examiner.exam_type_selection', ['header_title'=>'FCS Orthopaedic - Select Exam Type','exam_name'=>'Orthopaedic','exam_type'=>'orthopaedic']); }
    public function paediatricSelection() { return view('examiner.exam_type_selection', ['header_title'=>'FCS Paediatric - Select Exam Type','exam_name'=>'Paediatric Surgery','exam_type'=>'paediatric']); }
    public function entSelection() { return view('examiner.exam_type_selection', ['header_title'=>'FCS ENT - Select Exam Type','exam_name'=>'ENT Surgery','exam_type'=>'ent']); }
    public function plasticSurgerySelection() { return view('examiner.exam_type_selection', ['header_title'=>'FCS Plastic Surgery - Select Exam Type','exam_name'=>'Plastic Surgery','exam_type'=>'plastic_surgery']); }
    public function neurosurgerySelection() { return view('examiner.exam_type_selection', ['header_title'=>'FCS Neurosurgery - Select Exam Type','exam_name'=>'Neurosurgery','exam_type'=>'neurosurgery']); }
    public function paediatricOrthopaedicsSelection() { return view('examiner.exam_type_selection', ['header_title'=>'FCS Paediatric Orthopaedics - Select Exam Type','exam_name'=>'Paediatric Orthopaedics','exam_type'=>'paediatric_orthopaedics']); }

    public function cardiothoracicClinicalForm() { return $this->loadExamForm('cardiothoracic', 'clinical', 1, 1); }
    public function neurosurgeryClinicalForm()   { return $this->loadExamForm('neurosurgery',   'clinical', 3, 2); }
    public function orthopaedicClinicalForm()    { return $this->loadExamForm('orthopaedic',    'clinical', 4, 1); }
    public function entClinicalForm()            { return $this->loadExamForm('ent',             'clinical', 5, 1); }
    public function paediatricOrthopaedicsClinicalForm() { return $this->loadExamForm('paediatric_orthopaedics', 'clinical', 6, 1); }
    public function paediatricClinicalForm()     { return $this->loadExamForm('paediatric',      'clinical', 7, 1); }
    public function plasticSurgeryClinicalForm() { return $this->loadExamForm('plastic_surgery', 'clinical', 8, 2); }
    public function urologyClinicalForm()        { return $this->loadExamForm('urology',          'clinical', 9, 2); }
    public function cardiothoracicVivaForm()     { return $this->loadExamForm('cardiothoracic',  'viva', 1, 2); }
    public function neurosurgeryVivaForm()       { return $this->loadExamForm('neurosurgery',    'viva', 3, 2); }
    public function orthopaedicVivaForm()        { return $this->loadExamForm('orthopaedic',     'viva', 4, 2); }
    public function entVivaForm()                { return $this->loadExamForm('ent',              'viva', 5, 2); }
    public function paediatricOrthopaedicsVivaForm() { return $this->loadExamForm('paediatric_orthopaedics', 'viva', 6, 2); }
    public function paediatricVivaForm()         { return $this->loadExamForm('paediatric',       'viva', 7, 2); }
    public function plasticSurgeryVivaForm()     { return $this->loadExamForm('plastic_surgery',  'viva', 8, 2); }
    public function urologyVivaForm()            { return $this->loadExamForm('urology',           'viva', 9, 2); }

    private function loadExamForm($examType, $formType, $programmeId, $casesCount)
    {
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');
        $currentYearId = User::getCurrentYearId();
        $currentYear = DB::table('years')->where('id', $currentYearId)->value('year_name');
        $programmeMap = ['cardiothoracic'=>1,'neurosurgery'=>3,'orthopaedic'=>4,'ent'=>5,'paediatric_orthopaedics'=>6,'paediatric'=>7,'plastic_surgery'=>8,'urology'=>9];
        $programmeId = $programmeMap[$examType] ?? null;
        $groupsWithCandidates = DB::table('candidates')->join('examiners_groups', 'candidates.group_id', '=', 'examiners_groups.id')->where('candidates.programme_id', $programmeId)->where('candidates.exam_year', $currentYear)->whereNotNull('candidates.group_id')->select('examiners_groups.id', 'examiners_groups.group_name')->distinct()->get();
        $examNames = ['cardiothoracic'=>'Cardiothoracic Surgery','urology'=>'Urology','orthopaedic'=>'Orthopaedic','paediatric'=>'Paediatric Surgery','ent'=>'ENT Surgery','plastic_surgery'=>'Plastic Surgery','neurosurgery'=>'Neurosurgery','paediatric_orthopaedics'=>'Paediatric Orthopaedics'];
        return view('examiner.universal_exam_form', ['header_title'=>'FCS '.$examNames[$examType].' - '.ucfirst($formType),'exam_name'=>$examNames[$examType],'exam_type'=>$examType,'form_type'=>$formType,'cases_count'=>$casesCount,'programme_id'=>$programmeId,'groups'=>$groupsWithCandidates]);
    }

    public function getExamCandidatesByGroup($examType, $groupId)
    {
        $currentYearId = User::getCurrentYearId();
        $currentYear = DB::table('years')->where('id', $currentYearId)->value('year_name');
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');
        if (!$examinerId) return response()->json(['error' => 'Examiner not found']);

        $programmeMap = ['cardiothoracic'=>1,'neurosurgery'=>3,'orthopaedic'=>4,'ent'=>5,'paediatric_orthopaedics'=>6,'paediatric'=>7,'plastic_surgery'=>8,'urology'=>9];
        $programmeId = $programmeMap[$examType] ?? null;
        if (!$programmeId) return response()->json(['error' => 'Invalid exam type']);

        $candidates = DB::table('candidates')->join('users', 'candidates.user_id', '=', 'users.id')
            ->where('candidates.programme_id', $programmeId)->where('candidates.group_id', $groupId)
            ->where('candidates.exam_year', $currentYear)->where('users.is_deleted', 0)
            ->select('candidates.id as candidates_id', 'candidates.candidate_id', 'users.name')
            ->orderBy('candidates.id', 'asc')->get();

        return response()->json($candidates);
    }

    public function submitExamEvaluation(Request $request)
    {
        $examiner = DB::table('examiners')->where('user_id', Auth::id())->first();
        if (!$examiner) return back()->with('error', 'Examiner data not found.');

        $currentYearId = User::getCurrentYearId();
        $tableMap = ['cardiothoracic'=>'cardiothoracic_results','urology'=>'urology_results','paediatric'=>'paediatric_results','ent'=>'ent_results','orthopaedic'=>'orthopaedic_results','plastic_surgery'=>'plastic_surgery_results','neurosurgery'=>'neurosurgery_results','paediatric_orthopaedics'=>'paediatric_orthopaedics_results'];
        $tableName = $tableMap[$request->exam_type] ?? null;
        if (!$tableName) return back()->with('error', 'Invalid exam type.');

        $existingSubmission = DB::table($tableName)->where('candidate_id', $request->candidate_id)->where('examiner_id', $examiner->id)->where('station_id', $request->station_id)->where('exam_format', $request->form_type)->where('exam_year', $currentYearId)->first();
        if ($existingSubmission) return back()->with('error', 'Candidate marks already submitted for this station and format. Please use Resubmit to edit.');

        $questionMarks = [];
        foreach ($request->all() as $key => $value) {
            if (strpos($key, 'question_marks_case') === 0 && is_array($value)) {
                foreach ($value as $index => $mark) {
                    if ($mark === "" || $mark === null) { $questionMarks[] = 0; continue; }
                    $questionMarks[] = $mark;
                }
            }
        }

        DB::table($tableName)->insert(['candidate_id'=>$request->candidate_id,'examiner_id'=>$examiner->id,'station_id'=>$request->station_id,'group_id'=>$request->group_id,'exam_format'=>$request->form_type,'question_mark'=>json_encode($questionMarks),'total'=>$request->total_marks,'remarks'=>$request->remarks,'exam_year'=>$currentYearId,'created_at'=>now(),'updated_at'=>now()]);

        return redirect()->back()->with('success', ucfirst($request->exam_type) . ' evaluation submitted successfully.');
    }
}
