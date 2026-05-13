<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Trainee;
use App\Models\HospitalModel;
use App\Models\Programme;
use App\Models\Country;
use App\Models\Candidates;
use Illuminate\Support\Facades\Auth;
use App\Models\CandidatesFormModel;
use App\Models\GeneralSurgery;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CandidatesImport;

class CandidatesController extends Controller
{
    // ── Analytics / Visual Reports ────────────────────────────────────────────
    public function reports()
    {
        $data['header_title']    = 'Candidates Analytics';
        $data['filterCountries'] = DB::table('candidates as c')
            ->join('countries as co', 'co.id', '=', 'c.country_id')
            ->select('co.id', 'co.country_name')
            ->groupBy('co.id', 'co.country_name')->orderBy('co.country_name')->get();
        $data['filterProgrammes'] = DB::table('candidates as c')
            ->join('programmes as p', 'p.id', '=', 'c.programme_id')
            ->select('p.id', 'p.name')
            ->groupBy('p.id', 'p.name')->orderBy('p.name')->get();
        $data['filterYears'] = DB::table('candidates')
            ->whereNotNull('exam_year')->where('exam_year', '!=', '')
            ->select('exam_year')->groupBy('exam_year')->orderByDesc('exam_year')->pluck('exam_year');
        return view('admin.associates.candidates.reports', $data);
    }

    public function reportsData()
    {
        $countryId   = request('country_id');
        $programmeId = request('programme_id');
        // Default to the current exam year so the KPIs match the candidates list
        $year        = request('year', date('Y'));
        $gender      = request('gender');
        $feePaid     = request('fee_paid');

        $applyF = function ($q, $pfx = 'candidates') use ($countryId, $programmeId, $year, $gender, $feePaid) {
            if ($countryId)   $q->where("{$pfx}.country_id",   $countryId);
            if ($programmeId) $q->where("{$pfx}.programme_id", $programmeId);
            if ($year !== null && $year !== '') $q->where("{$pfx}.exam_year", $year);
            if ($gender)      $q->where("{$pfx}.gender",       $gender);
            if ($feePaid !== null && $feePaid !== '') $q->where("{$pfx}.fee_paid", $feePaid);
        };

        // ── KPIs ─────────────────────────────────────────────────────────────
        $q = DB::table('candidates'); $applyF($q);
        $total    = (clone $q)->count();
        $feePaidC = (clone $q)->where('fee_paid', 'Yes')->count();
        $male     = (clone $q)->where('gender', 'Male')->count();
        $female   = (clone $q)->where('gender', 'Female')->count();

        // ── By Country (top 15) ───────────────────────────────────────────────
        $byCountry = tap(
            DB::table('candidates as c')->join('countries as co', 'co.id', '=', 'c.country_id'),
            fn($q) => $applyF($q, 'c')
        )->select('co.country_name as label', DB::raw('COUNT(*) as value'))
         ->groupBy('co.country_name')->orderByDesc('value')->limit(15)->get();

        // ── By Programme ──────────────────────────────────────────────────────
        $byProgramme = tap(
            DB::table('candidates as c')->join('programmes as p', 'p.id', '=', 'c.programme_id'),
            fn($q) => $applyF($q, 'c')
        )->select('p.name as label', DB::raw('COUNT(*) as value'))
         ->groupBy('p.name')->orderByDesc('value')->get();

        // ── By Gender ─────────────────────────────────────────────────────────
        $byGender = tap(DB::table('candidates'), fn($q) => $applyF($q))
            ->select(DB::raw("COALESCE(NULLIF(gender,''),'Unknown') as label"), DB::raw('COUNT(*) as value'))
            ->groupBy('label')->get();

        // ── By Exam Year ──────────────────────────────────────────────────────
        $byYear = tap(DB::table('candidates'), fn($q) => $applyF($q))
            ->select('exam_year as label', DB::raw('COUNT(*) as value'))
            ->whereNotNull('exam_year')->where('exam_year', '!=', '')
            ->groupBy('exam_year')->orderBy('exam_year')->get();

        // ── Fee Paid breakdown ────────────────────────────────────────────────
        $byFeePaid = tap(DB::table('candidates'), fn($q) => $applyF($q))
            ->select(DB::raw("COALESCE(NULLIF(fee_paid,''),'Pending') as label"), DB::raw('COUNT(*) as value'))
            ->groupBy('label')->get();

        // ── Repeat status ─────────────────────────────────────────────────────
        $repeatStats = tap(DB::table('candidates'), fn($q) => $applyF($q))
            ->selectRaw("
                SUM(CASE WHEN repeat_paper_one='Yes' THEN 1 ELSE 0 END) as repeat_p1,
                SUM(CASE WHEN repeat_paper_two='Yes' THEN 1 ELSE 0 END) as repeat_p2,
                SUM(CASE WHEN mmed='Yes' THEN 1 ELSE 0 END) as mmed_qualified
            ")->first();

        // ── Country summary table (top 20) ────────────────────────────────────
        $countryTable = tap(
            DB::table('candidates as c')->join('countries as co', 'co.id', '=', 'c.country_id'),
            fn($q) => $applyF($q, 'c')
        )->select(
            'co.country_name',
            DB::raw('COUNT(*) as total'),
            DB::raw("SUM(CASE WHEN c.gender='Male' THEN 1 ELSE 0 END) as male"),
            DB::raw("SUM(CASE WHEN c.gender='Female' THEN 1 ELSE 0 END) as female"),
            DB::raw("SUM(CASE WHEN c.fee_paid='Yes' THEN 1 ELSE 0 END) as fee_paid")
        )->groupBy('co.country_name')->orderByDesc('total')->limit(20)->get();

        return response()->json([
            'kpi'          => compact('total', 'feePaidC', 'male', 'female'),
            'byCountry'    => $byCountry,
            'byProgramme'  => $byProgramme,
            'byGender'     => $byGender,
            'byYear'       => $byYear,
            'byFeePaid'    => $byFeePaid,
            'repeatStats'  => $repeatStats,
            'countryTable' => $countryTable,
        ]);
    }

    public function list()
    {
        $data['getRecord']       = User::getCandidates();
        $data['header_title']    = "Candidates List";
        $data['filterCountries'] = DB::table('candidates as c')
            ->join('countries as co', 'co.id', '=', 'c.country_id')
            ->select('co.country_name')->groupBy('co.country_name')->orderBy('co.country_name')
            ->where('c.exam_year', date('Y'))->pluck('co.country_name');
        $data['filterProgrammes'] = DB::table('candidates as c')
            ->join('programmes as p', 'p.id', '=', 'c.programme_id')
            ->select('p.name')->groupBy('p.name')->orderBy('p.name')
            ->where('c.exam_year', date('Y'))->pluck('p.name');
        $data['filterYears'] = DB::table('candidates')
            ->whereNotNull('exam_year')->where('exam_year', '!=', '')
            ->select('exam_year')->groupBy('exam_year')->orderByDesc('exam_year')->pluck('exam_year');
        return view('admin.associates.candidates.list', $data);
    }
    //Candidates List for examiners
    public function examinerList()
    {
        $data['getRecord'] = User::getexaminerCandidates();
        $data['header_title'] = "Candidates List";
        return view('examiner.candidates_list', $data);
    }

    public function view($id)
    {
        $candidate = User::getCandidates()->firstWhere('candidates_id', $id);
        if (!$candidate) {
            return redirect('admin/associates/candidates/list')->with('error', 'Candidate not found');
        }
        $header_title = "View Candidate";
        return view('admin.associates.candidates.view_candidate', compact('candidate', 'header_title'));

        // dd($trainee);
    }

    public function add()
    {
        $data['getHospital'] = HospitalModel::getHospital();
        $data['getProgramme'] = Programme::getProgramme();
        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Add New Candidate";
        return view('admin.associates.candidates.add', $data);
    }

    public function import()
    {

        $data['header_title'] = "Import Candidates";
        return view('admin.associates.candidates.import', $data);
    }


    public function importData(Request $request)
    {
        // Large imports can exceed the default 30s PHP limit
        set_time_limit(300);
        ini_set('max_execution_time', 300);

        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');

        try {
            $import = new CandidatesImport;
            Excel::import($import, $file);

            $imported = $import->getImportedCount();
            $skipped  = $import->getSkippedCount();
            $msg = "Import complete: {$imported} candidate(s) saved";
            if ($skipped > 0) $msg .= ", {$skipped} updated (already existed)";
            return redirect('admin/associates/candidates/list')->with('success', $msg);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function insert(Request $request)
    {
        $request->validate([
            'firstname'      => 'required|string|max:255',
            'lastname'       => 'required|string|max:255',
            'personal_email' => 'required|email|max:255',
            'programme_id'   => 'required|integer',
            'hospital_id'    => 'required|integer',
            'country_id'     => 'required|integer',
            'entry_number'   => 'required|string|max:255',
        ]);

        $fullName = trim($request->firstname . ' ' . ($request->middlename ?? '') . ' ' . $request->lastname);
        $userType = 3; // Candidate

        // Build login email: use provided email field, fall back to personal_email
        $loginEmail = $request->email ?: $request->personal_email;

        $user = User::create([
            'name'      => $fullName,
            'email'     => $loginEmail,
            'password'  => bcrypt($request->password ?: \Illuminate\Support\Str::random(12)),
            'user_type' => $userType,
        ]);

        DB::table('user_roles')->insert([
            'user_id'    => $user->id,
            'role_type'  => $userType,
            'is_active'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Candidates::create([
            'user_id'          => $user->id,
            'firstname'        => $request->firstname,
            'middlename'       => $request->middlename,
            'lastname'         => $request->lastname,
            'personal_email'   => $request->personal_email,
            'gender'           => $request->gender,
            'programme_id'     => $request->programme_id,
            'hospital_id'      => $request->hospital_id,
            'country_id'       => $request->country_id,
            'entry_number'     => $request->entry_number,
            'exam_number'      => $request->exam_number,
            'repeat_paper_one' => $request->repeat_paper_one ?? 'No',
            'repeat_paper_two' => $request->repeat_paper_two ?? 'No',
            'mmed'             => $request->mmed ?? 'No',
            'admission_year'   => $request->admission_year,
            'exam_year'        => $request->exam_year,
            'sponsor'          => $request->sponsor,
            'remarks'          => $request->remarks,
            'invoice_number'   => $request->invoice_number,
            'invoice_date'     => $request->invoice_date ?: null,
            'invoice_amount'   => $request->invoice_amount ?: null,
            'invoice_status'   => $request->invoice_status ?? 'Pending',
            'fee_paid'         => $request->fee_paid ?? 'No',
            'amount_paid'      => $request->amount_paid ?: null,
            'payment_date'     => $request->payment_date ?: null,
            'mode_of_payment'  => $request->mode_of_payment,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect('admin/associates/candidates/list')->with('success', 'Candidate added successfully.');
    }


    public function edit($id)
    {
        $candidate = User::getCandidates()->firstWhere('candidates_id', $id);
        if (!$candidate) {
            return redirect('admin/associates/candidates/list')->with('error', 'Candidate not found');
        }
        $data['getHospital'] = HospitalModel::getHospital();
        $data['getProgramme'] = Programme::getProgramme();
        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Edit Candidate";
        $data['candidate'] = $candidate;
        // dd($candidate);
        return view('admin.associates.candidates.edit_candidate', $data);
    }

    public function update(Request $request, $id)
    {
        $candidate = Candidates::find($id);
        if (!$candidate) {
            return redirect('admin/associates/candidates/list')->with('error', 'Candidate not found');
        }

        $user = User::find($candidate->user_id);
        $fullName = trim("{$request->firstname} {$request->middlename} {$request->lastname}");
        $user->name = $fullName;
        $user->email = $request->email;
        $user->password = $request->password;
        $user->save();

        $candidate->update([
            'firstname'        => $request->firstname,
            'middlename'       => $request->middlename,
            'lastname'         => $request->lastname,
            'personal_email'   => $request->personal_email,
            'gender'           => $request->gender,
            'programme_id'     => $request->programme_id,
            'hospital_id'      => $request->hospital_id,
            'country_id'       => $request->country_id,
            'entry_number'     => $request->entry_number,
            'candidate_id'     => $request->candidate_id ?: null,
            'exam_number'      => $request->exam_number ?: null,
            'repeat_paper_one' => $request->repeat_paper_one ?? 'No',
            'repeat_paper_two' => $request->repeat_paper_two ?? 'No',
            'admission_year'   => $request->admission_year,
            'exam_year'        => $request->exam_year,
            'mmed'             => $request->mmed ?? 'No',
            'invoice_number'   => $request->invoice_number,
            'invoice_date'     => $request->invoice_date ?: null,
            'invoice_status'   => $request->invoice_status,
            'invoice_amount'   => $request->invoice_amount ?: null,
            'sponsor'          => $request->sponsor,
            'fee_paid'         => $request->fee_paid ?? 'No',
            'amount_paid'      => $request->amount_paid ?: null,
            'payment_date'     => $request->payment_date ?: null,
            'mode_of_payment'  => $request->mode_of_payment ?: null,
            'remarks'          => $request->remarks ?: null,
        ]);

        // Keep matching trainee record in sync
        $this->syncToTrainee($candidate);

        return redirect('admin/associates/candidates/list')->with('success', 'Candidate updated successfully');
    }

    public function delete($id)
    {
        // Step 1: Find the user
        $user = User::find($id);

        if (!$user) {
            return redirect('admin/associates/candidates/list')->with('error', 'User not found');
        }

        // Step 2: Find the candidate
        $candidate = Candidates::where('user_id', $user->id)->first();
        if (!$candidate) {
            return redirect('admin/associates/candidates/list')->with('error', 'Candidate not found');
        }

        // Optional: Ensure the user is a candidate or trainee
        if (!in_array($user->user_type, [2, 3])) {
            return redirect('admin/associates/candidates/list')->with('error', 'User is not a trainee or candidate');
        }

        $user->save();

        // ✅ Step 4: Deactivate all associated roles regardless of role_type
        DB::table('user_roles')
            ->where('user_id', $user->id)
            ->update(['is_active' => 0]);

        return redirect('admin/associates/candidates/list')->with('success', 'Candidate deleted successfully');
    }


    ////// EXAMINER ROUTES///////
    public function mcsexaminerform()
    {
        // Get examiner's group IDs for the current year
        $examinerId = DB::table('examiners')
            ->where('user_id', Auth::id())
            ->value('id');

        $currentYearId = User::getCurrentYearId();

        $examinerGroupIds = DB::table('exams_groups')
            ->where('exm_id', $examinerId)
            ->where('year_id', $currentYearId)
            ->pluck('group_id')
            ->toArray();

        // Fetch ALL groups from the examiners_groups table (not filtered)
        $groups = DB::table('examiners_groups')->get();

        $data['header_title'] = 'MCS Form';
        $data['getRecord'] = User::getExaminerCandidates(null, $currentYearId);
        $data['groups'] = $groups;
        $data['examinerGroupIds'] = $examinerGroupIds; // Keep for reference if needed

        return view('examiner.examiner_form', $data);
    }

    // get Examiner GS Form
    public function gsexaminerform()
    {
        // Get examiner ID
        $examinerId = DB::table('examiners')
            ->where('user_id', Auth::id())
            ->value('id');

        // Get current exam year ID
        $currentYearId = User::getCurrentYearId();

        // Get examiner's group IDs for the current year (GS uses same mapping as MCS)
        $examinerGroupIds = DB::table('exams_groups')
            ->where('exm_id', $examinerId)
            ->where('year_id', $currentYearId)
            ->pluck('group_id')
            ->toArray();

        // Fetch ALL groups from examiners_groups (same as MCS)
        $groups = DB::table('examiners_groups')->get();

        $data['header_title'] = 'GS Form';
        $data['getRecord'] = User::getExaminerCandidates(null, $currentYearId); // SAME as MCS
        $data['groups'] = $groups;
        $data['examinerGroupIds'] = $examinerGroupIds;

        return view('examiner.general_surgery', $data);
    }

//    public function getGsCandidatesByGroup()
//    {
//        // Fetch candidates belonging to the selected group, having programme_id = 2, sorted by candidate_id
//        $candidates = DB::table('candidates')
//            ->where('programme_id', 2)
//            ->whereNotNull('candidate_id')
//            ->select('id as cand_id', 'candidate_id as c_id') // Use aliases for simpler frontend usage
//            ->orderBy('candidate_id', 'asc') // Sort by candidate_id in ascending order
//            ->get();
//
//        return response()->json($candidates);
//    }

    public function getMcsCandidatesByGroup($groupId)
    {
        // Get current year
        $currentYearId = User::getCurrentYearId();
        $currentYear = \DB::table('years')
            ->where('id', $currentYearId)
            ->value('year_name'); // or 'year' - adjust based on your table

        // Get examiner ID (just for logging/tracking, not for access control)
        $examinerId = DB::table('examiners')
            ->where('user_id', Auth::id())
            ->value('id');

        if (!$examinerId) {
            return response()->json(['error' => 'Examiner not found']);
        }

        // Fetch candidates for this group and current year (NO access check)
        $candidates = DB::table('candidates')
            ->join('users', 'candidates.user_id', '=', 'users.id')
            ->where('candidates.programme_id', 10)
            ->where('candidates.group_id', $groupId)
            ->where('candidates.exam_year', $currentYear)
            ->where('users.is_deleted', '0')
            ->select(
                'candidates.id as candidates_id',
                'candidates.candidate_id',
                'users.name',
                'candidates.exam_year',
                'candidates.group_id'
            )
            ->orderBy('candidates.id', 'asc')
            ->get();

        return response()->json($candidates);
    }

    public function getGsCandidatesByGroup($groupId)
    {
        $currentYearId = User::getCurrentYearId();

        $currentYear = DB::table('years')
            ->where('id', $currentYearId)
            ->value('year_name');

        $examinerId = DB::table('examiners')
            ->where('user_id', Auth::id())
            ->value('id');

        if (!$examinerId) {
            return response()->json(['error' => 'Examiner not found']);
        }

        // IMPORTANT: No group filter here
        $candidates = DB::table('candidates')
            ->join('users', 'candidates.user_id', '=', 'users.id')
            ->where('candidates.programme_id', 2)
            ->where('candidates.exam_year', $currentYear)
            ->where('users.is_deleted', '0')
            ->select(
                'candidates.id as candidates_id',
                'candidates.candidate_id',
                'users.name',
                'candidates.exam_year'
            )
            ->orderBy('candidates.id', 'asc')
            ->get();

        return response()->json($candidates);
    }



//    public function storeEvaluation(Request $request)
//    {
//        // Get the logged-in user's ID
//        $loggedInUserId = Auth::id();
//
//        $examiner = DB::table('examiners')->where('user_id', $loggedInUserId)->first();
//
//        if (!$examiner) {
//            return back()->with('error', 'Examiner data not found.');
//        }
//
//        $examinerId = $examiner->id;
//        $examinerGroupId = $examiner->group_id;
//        $questionMarksJson = json_encode($request->input('question_marks'));
//
//        $evaluation = new CandidatesFormModel();
//        $evaluation->candidate_id = $request->input('candidate_id');
//        $evaluation->examiner_id = $examinerId;
//        $evaluation->station_id = $request->input('station_id');
//        $evaluation->group_id = $examinerGroupId;
//        $evaluation->question_mark = $questionMarksJson;
//        $evaluation->total = $request->input('total_marks');
//        $evaluation->overall = strtolower($request->input('overall'));
//        $evaluation->remarks = $request->input('remarks');
//
//        // dd($evaluation);
//
//        $evaluation->save();
//
//        return redirect()->back()->with('success', 'Evaluation submitted successfully.');
//    }

    private function getExaminerGroupIds($examinerId, $examYear)
    {
        return DB::table('exams_groups')
            ->where('exm_id', $examinerId)
            ->where('year_id', $examYear)
            ->pluck('group_id')
            ->toArray();
    }

    public function storeEvaluation(Request $request)
    {
        $examiner = DB::table('examiners')->where('user_id', Auth::id())->first();
        if (!$examiner) return back()->with('error', 'Examiner data not found.');

        $currentYearId = User::getCurrentYearId();
        $groupId = $request->group_id;

        // ✅ CHECK FOR EXISTING SUBMISSION
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
        $evaluation->group_id = $groupId;
        $evaluation->question_mark = json_encode($request->question_marks);
        $evaluation->total = $request->total_marks;
        $evaluation->overall = strtolower($request->overall);
        $evaluation->remarks = $request->remarks;
        $evaluation->exam_year = $currentYearId;

        $evaluation->save();

        return redirect()->back()->with('success', 'Evaluation submitted successfully.');
    }


    //GS Data SUBMIT.
    public function storegsEvaluation(Request $request)
    {
        $examiner = DB::table('examiners')->where('user_id', Auth::id())->first();

        if (!$examiner) {
            return back()->with('error', 'Examiner data not found.');
        }

        $currentYearId = User::getCurrentYearId();
        $groupId = $request->group_id;

        // ✅ CHECK FOR EXISTING SUBMISSION
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
        $evaluation->group_id = $groupId;
        $evaluation->question_mark = json_encode($request->question_marks);
        $evaluation->total = $request->total_marks;
        $evaluation->remarks = $request->remarks;
        $evaluation->exam_year = $currentYearId;

        $evaluation->save();

        return redirect()->back()->with('success', 'GS Evaluation submitted successfully.');
    }

   //Results functions
    public function results()
    {
        $examinerId = \DB::table('examiners')->where('user_id', Auth::id())->value('id');
        if (!$examinerId) {
            return back()->with('error', 'Examiner data not found.');
        }

        $currentExamYearId = User::getCurrentYearId();

        // Define all table categories
        $mcsTable = 'mcs_results';
        $gsTable = 'gs_results';
        $fcsGroupedTables = [
            'cardiothoracic_results',
            'orthopaedic_results',
            'ent_results',
            'urology_results',
            'neurosurgery_results',
            'paediatric_orthopaedics_results',
            'paediatric_results',
            'plastic_surgery_results'
        ];

        $mcsResults = collect();
        $gsResults = collect();
        $fcsResults = collect();

        // Track timestamps
        $lastMcsTimestamp = null;
        $lastGsTimestamp = null;
        $lastFcsTimestamp = null;
        $lastFcsTable = null;

        // PROCESS MCS RESULTS
        if (\Schema::hasTable($mcsTable)) {
            $latestMcs = \DB::table($mcsTable)
                ->where('examiner_id', $examinerId)
                ->where('exam_year', $currentExamYearId)
                ->latest('created_at')
                ->first();

            if ($latestMcs) {
                $lastMcsTimestamp = $latestMcs->created_at;
            }

            $mcsResults = \DB::table($mcsTable)
                ->select(
                    "$mcsTable.*",
                    "$mcsTable.id as record_id",
                    "candidates.candidate_id as candidate_name",
                    "examiners_groups.group_name"
                )
                ->join('candidates', "$mcsTable.candidate_id", '=', 'candidates.id')
                ->join('examiners_groups', "$mcsTable.group_id", '=', 'examiners_groups.id')
                ->where("$mcsTable.examiner_id", $examinerId)
                ->where("$mcsTable.exam_year", $currentExamYearId)
                ->get();
        }

        // PROCESS GS RESULTS
        if (\Schema::hasTable($gsTable)) {
            $latestGs = \DB::table($gsTable)
                ->where('examiner_id', $examinerId)
                ->where('exam_year', $currentExamYearId)
                ->latest('created_at')
                ->first();

            if ($latestGs) {
                $lastGsTimestamp = $latestGs->created_at;
            }

            $gsResults = \DB::table($gsTable)
                ->select(
                    "$gsTable.*",
                    "$gsTable.id as record_id",
                    "candidates.candidate_id as candidate_name",
                    "examiners_groups.group_name"
                )
                ->join('candidates', "$gsTable.candidate_id", '=', 'candidates.id')
                ->join('examiners_groups', "$gsTable.group_id", '=', 'examiners_groups.id')
                ->where("$gsTable.examiner_id", $examinerId)
                ->where("$gsTable.exam_year", $currentExamYearId)
                ->get();
        }

        // PROCESS FCS SPECIALTY RESULTS
        foreach ($fcsGroupedTables as $table) {
            if (!\Schema::hasTable($table)) continue;

            $latest = \DB::table($table)
                ->where('examiner_id', $examinerId)
                ->where('exam_year', $currentExamYearId)
                ->latest('created_at')
                ->first();

            if ($latest && (!$lastFcsTimestamp || $latest->created_at > $lastFcsTimestamp)) {
                $lastFcsTimestamp = $latest->created_at;
                $lastFcsTable = $table;
            }

            $records = \DB::table($table)
                ->select(
                    "$table.*",
                    "$table.id as record_id",
                    "candidates.candidate_id as candidate_name",
                    "examiners_groups.group_name",
                    \DB::raw("'$table' as source_table")
                )
                ->join('candidates', "$table.candidate_id", '=', 'candidates.id')
                ->join('examiners_groups', "$table.group_id", '=', 'examiners_groups.id')
                ->where("$table.examiner_id", $examinerId)
                ->where("$table.exam_year", $currentExamYearId)
                ->get();

            $fcsResults = $fcsResults->merge($records);
        }

        // Determine what to display
        $showMcs = !$mcsResults->isEmpty();
        $showGs = !$gsResults->isEmpty();
        $showFcs = !$fcsResults->isEmpty();

        // Find the most recent submission overall
        $allTimestamps = collect([
            ['type' => 'mcs', 'timestamp' => $lastMcsTimestamp],
            ['type' => 'gs', 'timestamp' => $lastGsTimestamp],
            ['type' => 'fcs', 'timestamp' => $lastFcsTimestamp],
        ])->filter(function($item) {
            return $item['timestamp'] !== null;
        })->sortByDesc('timestamp');

        $mostRecentType = $allTimestamps->first()['type'] ?? null;

        // Process FCS results for grouped display
        $fcsGroupedResults = collect();
        if ($showFcs) {
            $fcsGroupedResults = $fcsResults
                ->groupBy(function($row) {
                    return $row->candidate_id . '-' . $row->source_table;
                })
                ->map(function ($records) {
                    $clinical = $records->where('exam_format', 'clinical');
                    $viva = $records->where('exam_format', 'viva');

                    return (object)[
                        'candidate_id' => $records->first()->candidate_id,
                        'candidate_name' => $records->first()->candidate_name,
                        'group_name' => $records->first()->group_name,
                        'station_id' => $records->first()->station_id ?? 0,
                        'records' => $records,
                        'clinical_total' => $clinical->sum('total'),
                        'viva_total' => $viva->sum('total'),
                        'overall_total' => $records->sum('total'),
                        'formats' => $records->groupBy('exam_format'),
                        'source_table' => $records->first()->source_table,
                    ];
                })
                ->values();
        }

        // DECISION: Show results based on most recent submission
        // If MCS was submitted most recently, show ONLY MCS
        // Otherwise show GS and/or FCS results together

        if ($mostRecentType === 'mcs') {
            return view('examiner.results', [
                'mcsResults' => $mcsResults,
                'gsResults' => collect(), // Empty collection
                'fcsResults' => collect(), // Empty collection
                'showMcs' => true,
                'showGs' => false,
                'showFcs' => false,
                'lastSubmittedForm' => $mcsTable,
                'header_title' => 'Results Summary'
            ]);
        }

        // Show combined view with GS and/or FCS (but not MCS)
        return view('examiner.results', [
            'mcsResults' => collect(), // Empty collection
            'gsResults' => $gsResults,
            'fcsResults' => $fcsGroupedResults,
            'showMcs' => false,
            'showGs' => $showGs,
            'showFcs' => $showFcs,
            'lastSubmittedForm' => $mostRecentType === 'gs' ? $gsTable : $lastFcsTable,
            'header_title' => 'Results Summary'
        ]);
    }



    // Helper array for all programme tables
    protected function getProgrammeTables(): array
    {
        return [
            'mcs_results' => ['grade_field' => 'overall'],
            'gs_results' => ['grade_field' => null],
            'cardiothoracic_results' => ['grade_field' => 'grade'],
            'ent_results' => ['grade_field' => 'grade'],
            'urology_results' => ['grade_field' => 'grade'],
            'neurosurgery_results' => ['grade_field' => 'grade'],
            'plastics_results' => ['grade_field' => 'grade'],
            'paediatrics_results' => ['grade_field' => 'grade'],
            'orthopaedic_results' => ['grade_field' => 'grade'],
        ];
    }

// =================== viewCandidateResults ===================
// For FCS programmes (clinical + viva) - no station_id needed
    public function viewFcsResults($candidate_id)
    {
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');
        if (!$examinerId) return back()->with('error', 'Examiner data not found.');

        $currentExamYearId = User::getCurrentYearId();

        $fcsTablesWithFormat = [
            'cardiothoracic_results',
            'urology_results',
            'paediatric_results',
            'ent_results',
            'plastic_surgery_results',
            'neurosurgery_results',
            'paediatric_orthopaedics_results',
            'fcs_results'
        ];

        $candidateRecords = collect();
        $sourceTable = null;

        // Search through FCS tables only
        foreach ($fcsTablesWithFormat as $table) {
            if (!\Schema::hasTable($table)) continue;

            $records = DB::table($table)
                ->select(
                    "$table.*",
                    'candidates.id as candidate_id',
                    'candidates.candidate_id as candidate_name',
                    'examiners.id as examiner_id',
                    'examiners_groups.group_name as group_name',
                    DB::raw("'$table' as source_table")
                )
                ->join('candidates', "$table.candidate_id", '=', 'candidates.id')
                ->join('examiners', "$table.examiner_id", '=', 'examiners.id')
                ->join('examiners_groups', "$table.group_id", '=', 'examiners_groups.id')
                ->where('candidates.id', $candidate_id)
                ->where('examiners.id', $examinerId)
                ->where("$table.exam_year", $currentExamYearId)
                ->get();

            if ($records->isNotEmpty()) {
                $candidateRecords = $candidateRecords->merge($records);
                $sourceTable = $table;
            }
        }

        if ($candidateRecords->isEmpty()) {
            return back()->with('error', 'No results found for this candidate.');
        }

        // Split records into clinical and viva
        $clinicalRecords = $candidateRecords->where('exam_format', 'clinical');
        $vivaRecords = $candidateRecords->where('exam_format', 'viva');

        $candidateResult = (object)[
            'candidate_id' => $candidateRecords->first()->candidate_id,
            'candidate_name' => $candidateRecords->first()->candidate_name,
            'group_name' => $candidateRecords->first()->group_name,
            'remarks'=>$candidateRecords->first()->remarks,
            'clinical_total' => $clinicalRecords->sum('total'),
            'viva_total' => $vivaRecords->sum('total'),
            'overall_total' => $candidateRecords->sum('total'),
            'clinical_records' => $clinicalRecords,
            'viva_records' => $vivaRecords,
        ];

        return view('examiner.view_fcs_results', [
            'candidateResult' => $candidateResult,
            'header_title' => "Candidate Full Results",
            'lastSource' => $sourceTable,
        ]);
    }

// For MCS/GS (question-based) - requires station_id
    public function viewCandidateResults($candidate_id, $station_id)
    {
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');
        if (!$examinerId) return back()->with('error', 'Examiner data not found.');

        $currentExamYearId = User::getCurrentYearId();

        $questionTables = ['mcs_results', 'gs_results'];
        $candidateRecords = collect();
        $sourceTable = null;

        // Search through question-based tables only
        foreach ($questionTables as $table) {
            if (!\Schema::hasTable($table)) continue;

            $records = DB::table($table)
                ->select(
                    "$table.*",
                    'candidates.id as candidate_id',
                    'candidates.candidate_id as candidate_name',
                    'examiners.id as examiner_id',
                    'examiners_groups.group_name as group_name',
                    DB::raw("'$table' as source_table")
                )
                ->join('candidates', "$table.candidate_id", '=', 'candidates.id')
                ->join('examiners', "$table.examiner_id", '=', 'examiners.id')
                ->join('examiners_groups', "$table.group_id", '=', 'examiners_groups.id')
                ->where('candidates.id', $candidate_id)
                ->where('examiners.id', $examinerId)
                ->where("$table.station_id", $station_id)
                ->where("$table.exam_year", $currentExamYearId)
                ->first();

            if ($records) {
                $candidateRecords->push($records);
                $sourceTable = $table;
                break;
            }
        }

        if ($candidateRecords->isEmpty()) {
            return back()->with('error', 'No results found for this candidate.');
        }

        $firstRecord = $candidateRecords->first();

        // Create candidateResult object for MCS/GS
        $candidateResult = (object)[
            'candidate_id' => $firstRecord->candidate_id,
            'candidate_name' => $firstRecord->candidate_name,
            'group_name' => $firstRecord->group_name,
            'station_id' => $firstRecord->station_id,
            'question_mark' => $firstRecord->question_mark,
            'total' => $firstRecord->total,
            'overall' => $firstRecord->overall ?? null,
            'remarks' => $firstRecord->remarks ?? '',
            'source_table' => $firstRecord->source_table,
        ];

        return view('examiner.view_results', [
            'candidateResult' => $candidateResult,
            'header_title' => "Candidate Results",
            'lastSource' => $sourceTable,
        ]);
    }



    // =================== resubmit ===================
    // =================== resubmit ===================
    public function resubmit($candidate_id, $station_id)
    {
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');
        if (!$examinerId) return redirect()->back()->with('error', 'Examiner data not found.');

        $currentExamYearId = User::getCurrentYearId();

        // Define question-based and FCS tables
        $questionTables = ['mcs_results', 'gs_results'];
        $fcsTablesWithFormat = [
            'cardiothoracic_results',
            'urology_results',
            'paediatric_results',
            'ent_results',
            'plastic_surgery_results',
            'neurosurgery_results',
            'paediatric_orthopaedics_results',
            'orthopaedic_results',
            'fcs_results'
        ];

        $candidateRecords = collect();
        $sourceTable = null;

        // Search through ALL tables to find this candidate's results
        $allTables = array_merge($questionTables, $fcsTablesWithFormat);

        foreach ($allTables as $table) {
            if (!\Schema::hasTable($table)) continue;

            $records = DB::table($table)
                ->select(
                    "$table.*",
                    'candidates.id as candidates_id',
                    'candidates.candidate_id as candidate_name',
                    'examiners.id as examiner_id',
                    'examiners_groups.id as group_table_id',
                    'examiners_groups.group_name',
                    DB::raw("'$table' as source_table")
                )
                ->join('candidates', "$table.candidate_id", '=', 'candidates.id')
                ->join('examiners', "$table.examiner_id", '=', 'examiners.id')
                ->join('examiners_groups', "$table.group_id", '=', 'examiners_groups.id')
                ->where('candidates.id', $candidate_id)
                ->where('examiners.id', $examinerId)
                ->where("$table.station_id", $station_id)
                ->where("$table.exam_year", $currentExamYearId)
                ->get();

            if ($records->isNotEmpty()) {
                $candidateRecords = $candidateRecords->merge($records);
                $sourceTable = $table;
                break; // Found the records, no need to continue
            }
        }

        if ($candidateRecords->isEmpty()) {
            return redirect()->back()->with('error', 'Candidate evaluation not found.');
        }

        $candidate = $candidateRecords->first();

        // Extract group identifier from group_name
        if (isset($candidate->group_name)) {
            $candidate->g_id = trim(str_replace(['Group', 'group'], '', $candidate->group_name));
        } else {
            $candidate->g_id = null;
        }

        // Decode question marks if present
        if (isset($candidate->question_mark)) {
            $candidate->question_mark = json_decode($candidate->question_mark, true);
        }

        // Get the full candidate record
        $fullCandidate = DB::table('candidates')->where('id', $candidate_id)->first();

        // ✅ SEPARATE GS AND MCS FORMS
        if ($sourceTable === 'gs_results') {
            // GS Results - use gsresubmit blade
            return view('examiner.gsresubmit', [
                'candidate' => $candidate,
                'header_title' => 'Resubmit GS Evaluation'
            ]);
        } elseif ($sourceTable === 'mcs_results') {
            // MCS Results - use resubmit blade
            return view('examiner.resubmit', [
                'candidate' => $candidate,
                'header_title' => 'Resubmit MCS Evaluation'
            ]);
        } elseif (in_array($sourceTable, $fcsTablesWithFormat)) {
            // FCS programme with clinical/viva - redirect to selection page
            $exam_name = DB::table('programmes')
                ->where('id', $fullCandidate->programme_id)
                ->value('programme_name') ?? 'Unknown Exam';

            return view('examiner.fcs_resubmit_selection', [
                'candidate' => $fullCandidate,
                'exam_name' => $exam_name,
                'header_title' => 'Resubmit Clinical + Viva'
            ]);
        }

        // Fallback (shouldn't reach here)
        return redirect()->back()->with('error', 'Unknown exam type.');
    }

// =================== updateEvaluation ===================
    public function updateEvaluation(Request $request, $candidate_id, $station_id)
    {
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');
        if (!$examinerId) return redirect()->back()->with('error', 'Examiner data not found.');

        $tables = $this->getProgrammeTables();
        $evaluation = null;
        $sourceTable = null;

        foreach ($tables as $table => $settings) {
            if (!\Schema::hasTable($table)) continue;
            $record = DB::table($table)
                ->where('candidate_id', $candidate_id)
                ->where('station_id', $station_id)
                ->where('examiner_id', $examinerId)
                ->first();

            if ($record) {
                $evaluation = $record;
                $sourceTable = $table;
                break;
            }
        }

        if (!$evaluation) return redirect()->back()->with('error', 'Evaluation not found.');

        $updateData = [
            'group_id' => $request->group_id,
            'station_id' => $request->station_id,
            'question_mark' => json_encode($request->question_marks ?? []),
            'total' => $request->total_marks,
            'remarks' => $request->remarks,
        ];

        if ($sourceTable === 'mcs_results' || $sourceTable !== 'gs_results') {
            $gradeField = $tables[$sourceTable]['grade_field'];
            if ($gradeField) $updateData[$gradeField] = $request->grade;
        }

        DB::table($sourceTable)
            ->where('candidate_id', $candidate_id)
            ->where('station_id', $station_id)
            ->where('examiner_id', $examinerId)
            ->update($updateData);

        return redirect('examiner/results')->with('success', 'Evaluation updated successfully.');
    }

    public function showFcsResubmitSelection($candidate_id)
    {
        // Get candidate details
        $candidate = DB::table('candidates')->where('id', $candidate_id)->first();

        if (!$candidate) {
            return redirect()->back()->with('error', 'Candidate not found.');
        }

        // Get the programme name
        $programmeName = DB::table('programmes')
            ->where('id', $candidate->programme_id)
            ->value('name') ?? 'Unknown Exam';

        // Pass variables to the Blade
        return view('examiner.fcs_resubmit_selection', [
            'candidate' => $candidate,
            'exam_name' => $programmeName,
            'header_title' => 'Resubmit Evaluation'

        ]);
    }

    public function showFcsResubmitForm($candidate_id, $exam_format)
    {
        $examinerId = DB::table('examiners')
            ->where('user_id', Auth::id())
            ->value('id');

        if (!$examinerId) {
            return redirect()->back()->with('error', 'Examiner data not found.');
        }

        $currentYearId = User::getCurrentYearId();

        // Examiner group IDs
        $examinerGroupIds = DB::table('exams_groups')
            ->where('exm_id', $examinerId)
            ->where('year_id', $currentYearId)
            ->pluck('group_id')
            ->toArray();

        $groups = DB::table('examiners_groups')->get();

        // Candidate
        $candidate = DB::table('candidates')->where('id', $candidate_id)->first();
        if (!$candidate) {
            return redirect()->back()->with('error', 'Candidate not found.');
        }

        // Programme → exam type map
        $programmeToExamType = [
            1 => 'cardiothoracic',
            3 => 'neurosurgery',
            4 => 'orthopaedic',
            5 => 'ent',
            6 => 'paediatric_orthopaedics',
            7 => 'paediatric',
            8 => 'plastic_surgery',
            9 => 'urology',
            2 => 'gs',
            10 => 'mcs'
        ];

        $exam_type = $programmeToExamType[$candidate->programme_id] ?? 'unknown';

        // ✅ FIXED: Match table names with your actual database AND getProgrammeTables()
        $fcsTablesWithFormat = [
            'cardiothoracic_results',
            'urology_results',
            'paediatric_results',      // ✅ Changed from 'paediatrics_results'
            'ent_results',
            'plastic_surgery_results',
            'neurosurgery_results',
            'paediatric_orthopaedics_results',
            'orthopaedic_results',
            'fcs_results',
        ];

        $existingRecord = null;
        $foundTable = null;

        // 🔥 SEARCH ALL FCS TABLES
        foreach ($fcsTablesWithFormat as $table) {
            if (!\Schema::hasTable($table)) {
                \Log::warning("Table does not exist: $table");
                continue;
            }

            $query = DB::table($table)
                ->where('candidate_id', $candidate_id)
                ->where('examiner_id', $examinerId)
                ->where('exam_year', $currentYearId)
                ->where('exam_format', $exam_format);

            $record = $query->first();

            // 🔥 DEBUG LOG
            \Log::info("Searching table: $table", [
                'candidate_id' => $candidate_id,
                'examiner_id' => $examinerId,
                'exam_year' => $currentYearId,
                'exam_format' => $exam_format,
                'found' => $record ? 'YES' : 'NO'
            ]);

            if ($record) {
                $existingRecord = $record;
                $foundTable = $table;
                break;
            }
        }

        // 🔥 If not found, let's check what actually exists for this candidate
        if (!$existingRecord) {
            \Log::error("No evaluation found for candidate", [
                'candidate_id' => $candidate_id,
                'examiner_id' => $examinerId,
                'exam_year' => $currentYearId,
                'exam_format' => $exam_format,
                'programme_id' => $candidate->programme_id,
                'exam_type' => $exam_type
            ]);

            // 🔥 Let's see what DOES exist for this candidate
            foreach ($fcsTablesWithFormat as $table) {
                if (!\Schema::hasTable($table)) continue;

                $anyRecords = DB::table($table)
                    ->where('candidate_id', $candidate_id)
                    ->where('examiner_id', $examinerId)
                    ->get();

                if ($anyRecords->isNotEmpty()) {
                    \Log::info("Found records in $table (but different format):", [
                        'records' => $anyRecords->toArray()
                    ]);
                }
            }

            return redirect()->back()->with(
                'error',
                "No existing evaluation found for this exam format. Please check logs for details."
            );
        }

        // Build candidate object for blade
        $candidateData = (object)[
            'candidates_id' => $candidate->id,
            'candidate_id' => $candidate->candidate_id,
            'candidate_name' => DB::table('users')->where('id', $candidate->user_id)->value('name'),
            'g_id' => $existingRecord->group_id,
            'g_name' => DB::table('examiners_groups')->where('id', $existingRecord->group_id)->value('group_name'),
            'station_id' => $existingRecord->station_id,
            'question_mark' => json_decode($existingRecord->question_mark, true),
            'total' => $existingRecord->total,
            'remarks' => $existingRecord->remarks,
            'grade' => $existingRecord->grade ?? ($existingRecord->overall ?? null),
        ];

        // ✅ CORRECT Case determination
        $casesCount = 2; // default for viva

        if ($exam_format === 'clinical') {
            // All clinical forms have 1 case EXCEPT plastic_surgery and neurosurgery
            if (in_array($exam_type, ['plastic_surgery', 'neurosurgery'])) {
                $casesCount = 2;
            } else {
                $casesCount = 1;
            }
        } elseif ($exam_type === 'cardiothoracic' && $exam_format === 'viva') {
            $casesCount = 4; // Only cardiothoracic viva has 4 cases
        }

        \Log::info('Resubmit Form Data:', [
            'exam_type' => $exam_type,
            'exam_format' => $exam_format,
            'cases_count' => $casesCount,
            'programme_id' => $candidate->programme_id,
            'found_in_table' => $foundTable
        ]);

        // Load universal multi-case form
        if (in_array($candidate->programme_id, [1, 3, 4, 5, 6, 7, 8, 9])) {
            return view('examiner.resubmit_form_universal', [
                'candidate' => $candidateData,
                'exam_name' => DB::table('programmes')->where('id', $candidate->programme_id)->value('name'),
                'exam_type' => $exam_type,
                'form_type' => $exam_format,
                'cases_count' => $casesCount,
                'programme_id' => $candidate->programme_id,
                'groups' => $groups,
                'examinerGroupIds' => $examinerGroupIds,
                'header_title' => 'Resubmit ' . ucfirst($exam_format),
                'isResubmit' => true
            ]);
        }

        // GS/MCS fallback
        return view('examiner.resubmit', [
            'candidate' => $candidateData,
            'header_title' => 'Resubmit Evaluation',
            'isResubmit' => true
        ]);
    }


    public function updateEvaluationFcs(Request $request, $candidate_id)
    {
        $examinerId = DB::table('examiners')->where('user_id', Auth::id())->value('id');
        if (!$examinerId) {
            return redirect()->back()->with('error', 'Examiner data not found.');
        }

        $currentYearId = User::getCurrentYearId();

        // FCS tables with exam_format column
        $fcsTablesWithFormat = [
            'cardiothoracic_results' => ['grade_field' => 'grade'],
            'urology_results' => ['grade_field' => 'grade'],
            'paediatric_results' => ['grade_field' => 'grade'],
            'ent_results' => ['grade_field' => 'grade'],
            'plastic_surgery_results' => ['grade_field' => 'grade'],
            'neurosurgery_results' => ['grade_field' => 'grade'],
            'paediatric_orthopaedics_results' => ['grade_field' => 'grade'],
            'orthopaedic_results' => ['grade_field' => 'grade'],
            'fcs_results' => ['grade_field' => 'grade'],
        ];

        $updated = false;

        // Get question marks directly from the request
        $questionMarks = $request->question_marks ?? [];

        // Validate that we have question marks
        if (empty($questionMarks)) {
            return redirect()->back()->with('error', 'No question marks provided.');
        }

        foreach ($fcsTablesWithFormat as $table => $settings) {
            if (!\Schema::hasTable($table)) continue;

            // Find the record to update
            $record = DB::table($table)
                ->where('candidate_id', $candidate_id)
                ->where('examiner_id', $examinerId)
                ->where('exam_format', $request->exam_format ?? $request->form_type)
                ->where('exam_year', $currentYearId)
                ->first();

            if (!$record) continue;

            $updateData = [
                'group_id' => $request->group_id ?? $record->group_id,
                'station_id' => $request->station_id ?? $record->station_id,
                'question_mark' => json_encode($questionMarks),
                'total' => $request->total_marks ?? array_sum($questionMarks),
                'remarks' => $request->remarks ?? $record->remarks,
                'updated_at' => now(),
            ];

            // Handle grade field if it exists
            $gradeField = $settings['grade_field'] ?? null;
            if ($gradeField && isset($request->grade)) {
                $updateData[$gradeField] = $request->grade;
            }

            DB::table($table)
                ->where('id', $record->id)
                ->update($updateData);

            $updated = true;

            \Log::info('FCS Evaluation Updated', [
                'table' => $table,
                'candidate_id' => $candidate_id,
                'question_marks' => $questionMarks,
                'total_marks' => $request->total_marks ?? array_sum($questionMarks),
                'record_id' => $record->id
            ]);

            break; // Only update one record
        }

        if (!$updated) {
            return redirect()->back()->with('error', 'No evaluation found to update.');
        }

        return redirect('examiner/results')->with('success', 'Evaluation updated successfully.');
    }
    //Exams With VIVA:::

// Selection pages for each exam type
    public function cardiothoracicSelection()
    {
        $data['header_title'] = 'FCS Cardiothoracic - Select Exam Type';
        $data['exam_name'] = 'Cardiothoracic Surgery';
        $data['exam_type'] = 'cardiothoracic';
        return view('examiner.exam_type_selection', $data);
    }

    public function urologySelection()
    {
        $data['header_title'] = 'FCS Urology - Select Exam Type';
        $data['exam_name'] = 'Urology';
        $data['exam_type'] = 'urology';
        return view('examiner.exam_type_selection', $data);
    }

    public function orthopaedicSelection()
    {
        $data['header_title'] = 'FCS Urology - Select Exam Type';
        $data['exam_name'] = 'Orthopaedic';
        $data['exam_type'] = 'orthopaedic';
        return view('examiner.exam_type_selection', $data);
    }


    public function paediatricSelection()
    {
        $data['header_title'] = 'FCS Paediatric - Select Exam Type';
        $data['exam_name'] = 'Paediatric Surgery';
        $data['exam_type'] = 'paediatric';
        return view('examiner.exam_type_selection', $data);
    }

    public function entSelection()
    {
        $data['header_title'] = 'FCS ENT - Select Exam Type';
        $data['exam_name'] = 'ENT Surgery';
        $data['exam_type'] = 'ent';
        return view('examiner.exam_type_selection', $data);
    }

    public function plasticSurgerySelection()
    {
        $data['header_title'] = 'FCS Plastic Surgery - Select Exam Type';
        $data['exam_name'] = 'Plastic Surgery';
        $data['exam_type'] = 'plastic_surgery';
        return view('examiner.exam_type_selection', $data);
    }

    public function neurosurgerySelection()
    {
        $data['header_title'] = 'FCS Neurosurgery - Select Exam Type';
        $data['exam_name'] = 'Neurosurgery';
        $data['exam_type'] = 'neurosurgery';
        return view('examiner.exam_type_selection', $data);
    }

    public function paediatricOrthopaedicsSelection()
    {
        $data['header_title'] = 'FCS Paediatric Orthopaedics - Select Exam Type';
        $data['exam_name'] = 'Paediatric Orthopaedics';
        $data['exam_type'] = 'paediatric_orthopaedics';
        return view('examiner.exam_type_selection', $data);
    }

// Clinical form methods
    public function cardiothoracicClinicalForm()
    {
        return $this->loadExamForm('cardiothoracic', 'clinical', 1, 1);
    }

    public function neurosurgeryClinicalForm()
    {
        return $this->loadExamForm('neurosurgery', 'clinical', 3, 2);
    }


    public function orthopaedicClinicalForm()
    {
        return $this->loadExamForm('orthopaedic', 'clinical', 4, 1);
    }

    public function entClinicalForm()
    {
        return $this->loadExamForm('ent', 'clinical', 5, 1);
    }

    public function paediatricOrthopaedicsClinicalForm()
    {
        return $this->loadExamForm('paediatric_orthopaedics', 'clinical', 6, 1);
    }

    public function paediatricClinicalForm()
    {
        return $this->loadExamForm('paediatric', 'clinical', 7, 1);
    }


    public function plasticSurgeryClinicalForm()
    {
        return $this->loadExamForm('plastic_surgery', 'clinical', 8, 2);
    }

    public function urologyClinicalForm()
    {
        return $this->loadExamForm('urology', 'clinical', 9, 2);
    }

// Viva form methods
    public function cardiothoracicVivaForm()
    {
        return $this->loadExamForm('cardiothoracic', 'viva', 1, 2);
    }

    public function neurosurgeryVivaForm()
    {
        return $this->loadExamForm('neurosurgery', 'viva', 3, 2);
    }

    public function orthopaedicVivaForm()
    {
        return $this->loadExamForm('orthopaedic', 'viva', 4, 2);
    }

    public function entVivaForm()
    {
        return $this->loadExamForm('ent', 'viva', 5, 2);
    }

    public function paediatricOrthopaedicsVivaForm()
    {
        return $this->loadExamForm('paediatric_orthopaedics', 'viva', 6, 2);
    }

    public function paediatricVivaForm()
    {
        return $this->loadExamForm('paediatric', 'viva', 7, 2);
    }

    public function plasticSurgeryVivaForm()
    {
        return $this->loadExamForm('plastic_surgery', 'viva', 8, 2);
    }

    public function urologyVivaForm()
    {
        return $this->loadExamForm('urology', 'viva', 9, 2);
    }


// Helper method to load exam forms
    private function loadExamForm($examType, $formType, $programmeId, $casesCount)
    {
        $examinerId = DB::table('examiners')
            ->where('user_id', Auth::id())
            ->value('id');

        $currentYearId = User::getCurrentYearId();
        $currentYear = DB::table('years')->where('id', $currentYearId)->value('year_name');

        // Map exam types to programme IDs
        $programmeMap = [
            'cardiothoracic' => 1,
            'neurosurgery' => 3,
            'orthopaedic' => 4,
            'ent' => 5,
            'paediatric_orthopaedics' => 6,
            'paediatric' => 7,
            'plastic_surgery' => 8,
            'urology' => 9
        ];

        $programmeId = $programmeMap[$examType] ?? null;

        // 🔥 Get groups that actually have candidates
        $groupsWithCandidates = DB::table('candidates')
            ->join('examiners_groups', 'candidates.group_id', '=', 'examiners_groups.id')
            ->where('candidates.programme_id', $programmeId)
            ->where('candidates.exam_year', $currentYear)
            ->where('candidates.group_id', '!=', null)
            ->select('examiners_groups.id', 'examiners_groups.group_name')
            ->distinct()
            ->get();

        // Prepare names
        $examNames = [
            'cardiothoracic' => 'Cardiothoracic Surgery',
            'urology' => 'Urology',
            'orthopaedic' => 'Orthopaedic',
            'paediatric' => 'Paediatric Surgery',
            'ent' => 'ENT Surgery',
            'plastic_surgery' => 'Plastic Surgery',
            'neurosurgery' => 'Neurosurgery',
            'paediatric_orthopaedics' => 'Paediatric Orthopaedics'
        ];

        return view('examiner.universal_exam_form', [
            'header_title' => 'FCS '.$examNames[$examType].' - '.ucfirst($formType),
            'exam_name' => $examNames[$examType],
            'exam_type' => $examType,
            'form_type' => $formType,
            'cases_count' => $casesCount,
            'programme_id' => $programmeId,
            'groups' => $groupsWithCandidates,  // 🔥 Only groups with candidates
        ]);
    }


// Get candidates for specific exam type
    public function getExamCandidatesByGroup($examType, $groupId)
    {
        $currentYearId = User::getCurrentYearId();
        $currentYear = DB::table('years')
            ->where('id', $currentYearId)
            ->value('year_name');

        $examinerId = DB::table('examiners')
            ->where('user_id', Auth::id())
            ->value('id');

        if (!$examinerId) {
            return response()->json(['error' => 'Examiner not found']);
        }

        $programmeMap = [
            'cardiothoracic' => 1,
            'neurosurgery' => 3,
            'orthopaedic' => 4,
            'ent' => 5,
            'paediatric_orthopaedics' => 6,
            'paediatric' => 7,
            'plastic_surgery' => 8,
            'urology' => 9
        ];

        $programmeId = $programmeMap[$examType] ?? null;
        if (!$programmeId) {
            return response()->json(['error' => 'Invalid exam type']);
        }

        // 🔥 Get ONLY candidates in the selected group
        $candidates = DB::table('candidates')
            ->join('users', 'candidates.user_id', '=', 'users.id')
            ->where('candidates.programme_id', $programmeId)
            ->where('candidates.group_id', $groupId)
            ->where('candidates.exam_year', $currentYear)
            ->where('users.is_deleted', 0)
            ->select(
                'candidates.id as candidates_id',
                'candidates.candidate_id',
                'users.name'
            )
            ->orderBy('candidates.id', 'asc')
            ->get();

        return response()->json($candidates);
    }


// Submit exam evaluation
    public function submitExamEvaluation(Request $request)
    {
        $examiner = DB::table('examiners')->where('user_id', Auth::id())->first();
        if (!$examiner) return back()->with('error', 'Examiner data not found.');

        $currentYearId = User::getCurrentYearId();
        $groupId = $request->group_id;

        // Determine table name
        $tableMap = [
            'cardiothoracic' => 'cardiothoracic_results',
            'urology' => 'urology_results',
            'paediatric' => 'paediatric_results',
            'ent' => 'ent_results',
            'orthopaedic' => 'orthopaedic_results',
            'plastic_surgery' => 'plastic_surgery_results',
            'neurosurgery' => 'neurosurgery_results',
            'paediatric_orthopaedics' => 'paediatric_orthopaedics_results'
        ];

        $tableName = $tableMap[$request->exam_type] ?? null;

        if (!$tableName) {
            return back()->with('error', 'Invalid exam type.');
        }

        // ✅ CHECK FOR EXISTING SUBMISSION
        $existingSubmission = DB::table($tableName)
            ->where('candidate_id', $request->candidate_id)
            ->where('examiner_id', $examiner->id)
            ->where('station_id', $request->station_id)
            ->where('exam_format', $request->form_type)
            ->where('exam_year', $currentYearId)
            ->first();

        if ($existingSubmission) {
            return back()->with('error', 'Candidate marks already submitted for this station and format. Please use Resubmit to edit.');
        }

        // ✅ ADD VALIDATION FOR UROLOGY AND PAEDIATRICS VIVA MARKS
        $questionMarks = [];
        foreach ($request->all() as $key => $value) {
            if (strpos($key, 'question_marks_case') === 0 && is_array($value)) {
                foreach ($value as $index => $mark) {
                    // Skip empty values (for optional questions)
                    if ($mark === "" || $mark === null) {
                        // For Urology and Paediatrics viva, question 3 can be empty
                        // For other exams or required questions, this should not happen
                        if (!(($request->exam_type == 'urology' || $request->exam_type == 'paediatric')
                            && $request->form_type == 'viva'
                            && $key == 'question_marks_case2[]'
                            && $index == 2)) { // 3rd question (0-indexed)
                            return back()->with('error', 'All marks are required except question 3 for Urology/Paediatrics viva.');
                        }
                        $questionMarks[] = 0; // Store 0 for empty optional questions
                        continue;
                    }

                    // For Urology and Paediatrics viva, ensure 0 is an accepted value
                    if (($request->exam_type == 'urology' || $request->exam_type == 'paediatric') && $request->form_type == 'viva') {
                        // Validate that the mark is either 0 or even numbers between 2-10
                        if ($mark != 0 && $mark != 2 && $mark != 4 && $mark != 6 && $mark != 8 && $mark != 10) {
                            return back()->with('error', 'Invalid mark value. For ' . ucfirst($request->exam_type) . ' viva, marks must be 0, 2, 4, 6, 8, or 10.');
                        }
                    } else {
                        // For other exams, validate 2-10 only
                        if (!in_array($mark, [2, 4, 6, 8, 10])) {
                            return back()->with('error', 'Invalid mark value. Marks must be 2, 4, 6, 8, or 10.');
                        }
                    }
                    $questionMarks[] = $mark;
                }
            }
        }

        DB::table($tableName)->insert([
            'candidate_id' => $request->candidate_id,
            'examiner_id' => $examiner->id,
            'station_id' => $request->station_id,
            'group_id' => $groupId,
            'exam_format' => $request->form_type,
            'question_mark' => json_encode($questionMarks),
            'total' => $request->total_marks,
            'remarks' => $request->remarks,
            'exam_year' => $currentYearId,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', ucfirst($request->exam_type) . ' evaluation submitted successfully.');
    }

    // ── Bidirectional sync helper ─────────────────────────────────────────────
    /**
     * After a candidate is updated, push shared fields to the matching trainee
     * record (matched by user_id). Only fires if a trainee row exists.
     */
    private function syncToTrainee(Candidates $candidate): void
    {
        $trainee = Trainee::where('user_id', $candidate->user_id)->first();
        if (!$trainee) {
            return;
        }

        $payload = [
            'firstname'       => $candidate->firstname,
            'middlename'      => $candidate->middlename,
            'lastname'        => $candidate->lastname,
            'personal_email'  => $candidate->personal_email,
            'gender'          => $candidate->gender,
            'entry_number'    => $candidate->entry_number,
            'sponsor'         => $candidate->sponsor,
            'exam_year'       => $candidate->exam_year ?: 0,
            'invoice_number'  => $candidate->invoice_number,
            'invoice_date'    => $candidate->invoice_date,
            'invoice_status'  => $candidate->invoice_status,
            'amount_paid'     => $candidate->amount_paid ?: 0,
            'payment_date'    => $candidate->payment_date,
            'mode_of_payment' => $this->normaliseMOP($candidate->mode_of_payment),
        ];

        // Only sync FK columns if the candidate has a valid (non-zero) value
        if (!empty((int) $candidate->programme_id)) {
            $payload['programme_id'] = $candidate->programme_id;
        }
        if (!empty((int) $candidate->hospital_id)) {
            $payload['hospital_id'] = $candidate->hospital_id;
        }
        if (!empty((int) $candidate->country_id)) {
            $payload['country_id'] = $candidate->country_id;
        }

        $trainee->update($payload);
    }

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
        return '';
    }

}
