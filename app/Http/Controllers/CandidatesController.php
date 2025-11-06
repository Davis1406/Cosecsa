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
    public function list()
    {
        $data['getRecord'] = User::getCandidates();
        $data['header_title'] = "Candidates List";
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
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');
        Excel::import(new CandidatesImport, $file);

        return redirect('admin/associates/candidates/list')->with('success', 'Candidates imported successfully');
    }

    public function insert(Request $request)
    {
        $fullName = trim("{$request->firstname} {$request->middlename} {$request->lastname}");
        $userType = 3; // Candidate

        $user = User::create([
            'name' => $fullName,
            'email' => $request->email,
            'password' => $request->password,
            'user_type' => $userType
        ]);

        // ✅ Insert into user_roles
        DB::table('user_roles')->insert([
            'user_id' => $user->id,
            'role_type' => $userType,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Candidates::create([
            'user_id' => $user->id,
            'firstname' => $request['firstname'],
            'middlename' => $request['middlename'],
            'lastname' => $request['lastname'],
            'personal_email' => $request['personal_email'],
            'gender' => $request['gender'],
            'status' => $request['status'],
            'programme_id' => $request['programme_id'],
            'hospital_id' => $request['hospital_id'],
            'country_id' => $request['country_id'],
            'group_id' => $request['candidate_id'],
            'repeat_P1' => $request['repeat_P1'],
            'repeat_P2' => $request['repeat_P2'],
            'mmed' => $request['mmed'],
            'entry_number' => $request['entry_number'],
            'admission_year' => $request['admission_year'],
            'exam_year' => $request['exam_year'],
            'invoice_number' => $request['invoice_number'],
            'invoice_date' => $request['invoice_date'],
            'invoice_status' => $request['invoice_status'],
            'sponsor' => $request['sponsor'],
            'amount_paid' => $request['amount_paid'],
        ]);

        return redirect('admin/associates/candidates/list')->with('success', 'Candidate added successfully');
    }


    public function edit($id)
    {
        $candidate = User::getCandidates()->firstWhere('candidate_id', $id);
        // if (!$candidate) {
        //     return redirect('admin/associates/candidates/list')->with('error', 'Candidate not found');
        // }
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
            'firstname' => $request->firstname,
            'middlename' => $request->middlename,
            'lastname' => $request->lastname,
            'personal_email' => $request->personal_email,
            'gender' => $request->gender,
            'programme_id' => $request->programme_id,
            'hospital_id' => $request->hospital_id,
            'country_id' => $request->country_id,
            'entry_number' => $request->entry_number,
            'repeat_paper_one' => $request->repeat_paper_one,
            'repeat_paper_two' => $request->repeat_paper_two,
            'admission_year' => $request->admission_year,
            'exam_year' => $request->exam_year,
            'mmed' => $request->mmed,
            'invoice_number' => $request->invoice_number,
            'invoice_date' => $request->invoice_date,
            'invoice_status' => $request->invoice_status,
            'sponsor' => $request->sponsor,
            'amount_paid' => $request->amount_paid,
        ]);

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
        $examinerGroupId = DB::table('examiners')
            ->where('user_id', Auth::id())
            ->value('group_id');

        // Fetch all groups from the examiners_groups table
        $groups = DB::table('examiners_groups')->get();

        $data['header_title'] = 'GS Form';
        $data['getRecord'] = User::getexaminerCandidates();
        $data['groups'] = $groups;
        $data['examinerGroupId'] = $examinerGroupId;

        return view('examiner.general_surgery', $data);
    }

    public function getGsCandidatesByGroup()
    {
        // Fetch candidates belonging to the selected group, having programme_id = 2, sorted by candidate_id
        $candidates = DB::table('candidates')
            ->where('programme_id', 2)
            ->whereNotNull('candidate_id')
            ->select('id as cand_id', 'candidate_id as c_id') // Use aliases for simpler frontend usage
            ->orderBy('candidate_id', 'asc') // Sort by candidate_id in ascending order
            ->get();

        return response()->json($candidates);
    }

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
    
    public function storeEvaluation(Request $request)
    {
        // Get the logged-in user's ID
        $loggedInUserId = Auth::id();

        $examiner = DB::table('examiners')->where('user_id', $loggedInUserId)->first();

        if (!$examiner) {
            return back()->with('error', 'Examiner data not found.');
        }

        $examinerId = $examiner->id;
        $examinerGroupId = $examiner->group_id;
        $questionMarksJson = json_encode($request->input('question_marks'));

        $evaluation = new CandidatesFormModel();
        $evaluation->candidate_id = $request->input('candidate_id');
        $evaluation->examiner_id = $examinerId;
        $evaluation->station_id = $request->input('station_id');
        $evaluation->group_id = $examinerGroupId;
        $evaluation->question_mark = $questionMarksJson;
        $evaluation->total = $request->input('total_marks');
        $evaluation->overall = strtolower($request->input('overall'));
        $evaluation->remarks = $request->input('remarks');

        // dd($evaluation);

        $evaluation->save();

        return redirect()->back()->with('success', 'Evaluation submitted successfully.');
    }


    //GS Data SUBMIT.
    public function storegsEvaluation(Request $request)
    {
        // Get the logged-in user's ID
        $loggedInUserId = Auth::id();

        $examiner = DB::table('examiners')->where('user_id', $loggedInUserId)->first();

        if (!$examiner) {
            return back()->with('error', 'Examiner data not found.');
        }

        $examinerId = $examiner->id;
        $examinerGroupId = $examiner->group_id;
        $questionMarksJson = json_encode($request->input('question_marks'));

        $evaluation = new GeneralSurgery();
        $evaluation->candidate_id = $request->input('candidate_id');
        $evaluation->examiner_id = $examinerId;
        $evaluation->station_id = $request->input('station_id');
        $evaluation->group_id = $request->input('group_id');
        $evaluation->question_mark = $questionMarksJson;
        $evaluation->total = $request->input('total_marks');
        $evaluation->remarks = $request->input('remarks');
        $evaluation->exam_year = date('Y');

        // dd($evaluation);

        $evaluation->save();

        return redirect()->back()->with('success', 'Evaluation submitted successfully.');
    }


    public function results(Request $request)
    {
        // Fetch examination results with the last submitted form
        $results = User::getExaminationResults();

        // Check if any records are found
        if ($results['records']->isEmpty()) {
            return redirect()->back()->with('error', 'No results found.');
        }

        // Get the last submitted form
        $lastSource = $results['lastSubmittedForm'];

        // Filter records to only include those from the last submitted form
        $filteredRecords = $results['records']->filter(function ($record) use ($lastSource) {
            return $record->source_table === $lastSource;
        });

        // Check if there are filtered records
        if ($filteredRecords->isEmpty()) {
            return redirect()->back()->with('error', 'No results found for the last submitted form.');
        }

        // Prepare data for the view
        $data['getRecord'] = $filteredRecords;
        $data['header_title'] = "Candidates Results";

        // Render the correct view based on the last submitted form
        if ($lastSource === 'gs_results') {
            return view('examiner.gsresults', $data);
        }

        return view('examiner.results', $data);
    }

    public function viewCandidateResults($candidate_id, $station_id)
    {
        // Get the logged-in examiner's user ID and corresponding examiner ID
        $loggedInUserId = Auth::id();
        $examiner = DB::table('examiners')->where('user_id', $loggedInUserId)->first();

        if (!$examiner) {
            return back()->with('error', 'Examiner data not found.');
        }

        $examinerId = $examiner->id;

        // Fetch the last submitted form source
        $lastSubmittedForm = DB::table('mcs_results')
            ->select('created_at', DB::raw("'mcs_results' as source_table"))
            ->where('examiner_id', $examinerId)
            ->union(
                DB::table('gs_results')
                    ->select('created_at', DB::raw("'gs_results' as source_table"))
                    ->where('examiner_id', $examinerId)
            )
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->first();

        if (!$lastSubmittedForm) {
            return back()->with('error', 'No submissions found for the examiner.');
        }

        // Fetch the results based on the last submitted form
        $data['candidateResult'] = null;
        $viewName = '';

        if ($lastSubmittedForm->source_table === 'mcs_results') {
            $data['candidateResult'] = DB::table('mcs_results')
                ->select(
                    'mcs_results.*',
                    'candidates.id as candidate_id',
                    'candidates.candidate_id as candidate_name',
                    'candidates.group_id as g_id',
                    'examiners.id as examiner_id',
                    'examiners_groups.group_name as group_name',
                    DB::raw("'mcs_results' as source_table") // Ensure source_table is included
                )
                ->join('candidates', 'mcs_results.candidate_id', '=', 'candidates.id')
                ->join('examiners', 'mcs_results.examiner_id', '=', 'examiners.id')
                ->join('examiners_groups', 'candidates.group_id', '=', 'examiners_groups.id')
                ->where('candidates.id', $candidate_id)
                ->where('examiners.id', $examinerId)
                ->where('mcs_results.station_id', $station_id)
                ->first();

            $viewName = 'examiner.view_results';
        } elseif ($lastSubmittedForm->source_table === 'gs_results') {
            $data['candidateResult'] = DB::table('gs_results')
                ->select(
                    'gs_results.*',
                    'candidates.id as candidate_id',
                    'candidates.candidate_id as candidate_name',
                    'candidates.group_id as g_id',
                    'gs_results.station_id as s_id',
                    'examiners.id as examiner_id',
                    'examiners_groups.group_name as g_name',
                    DB::raw("'gs_results' as source_table") // Ensure source_table is included
                )
                ->join('candidates', 'gs_results.candidate_id', '=', 'candidates.id')
                ->join('examiners', 'gs_results.examiner_id', '=', 'examiners.id')
                ->join('examiners_groups', 'gs_results.group_id', '=', 'examiners_groups.id')
                ->where('candidates.id', $candidate_id)
                ->where('examiners.id', $examinerId)
                ->where('gs_results.station_id', $station_id)
                ->first();

            $viewName = 'examiner.view_gs_results';
        }

        // Check if no result found
        if (!$data['candidateResult']) {
            return back()->with('error', 'No results found for the given candidate and station.');
        }

        // Set the page header title
        $data['header_title'] = "View Candidate";

        // Return the appropriate view with data
        return view($viewName, $data);
    }

    public function resubmit($candidate_id, $station_id)
    {
        // Get the logged-in examiner's user ID and corresponding examiner ID
        $loggedInUserId = Auth::id();
        $examiner = DB::table('examiners')->where('user_id', $loggedInUserId)->first();

        if (!$examiner) {
            return redirect()->back()->with('error', 'Examiner data not found.');
        }

        $examinerId = $examiner->id;
        $data['header_title'] = "Resubmit Results";

        // Fetch the candidate's record from `mcs_results`
        $candidateResult = DB::table('mcs_results')
            ->select(
                'mcs_results.*',
                'candidates.id as candidates_id',
                'candidates.candidate_id as candidate_name',
                'candidates.group_id as g_id',
                'examiners.id as examiner_id',
                'examiners_groups.group_name as group_name',
                DB::raw("'mcs_results' as source_table")
            )
            ->join('candidates', 'mcs_results.candidate_id', '=', 'candidates.id')
            ->join('examiners', 'mcs_results.examiner_id', '=', 'examiners.id')
            ->join('examiners_groups', 'candidates.group_id', '=', 'examiners_groups.id')
            ->where('mcs_results.candidate_id', $candidate_id)
            ->where('mcs_results.station_id', $station_id)
            ->where('examiners.id', $examinerId)
            ->first();

        // If not found in `mcs_results`, fetch from `gs_results`
        if (!$candidateResult) {
            $candidateResult = DB::table('gs_results')
                ->select(
                    'gs_results.*',
                    'candidates.id as candidates_id',
                    'candidates.candidate_id as candidate_name',
                    'gs_results.group_id as g_id',
                    'examiners.id as examiner_id',
                    'examiners_groups.group_name as g_name',
                    DB::raw("'gs_results' as source_table")
                )
                ->join('candidates', 'gs_results.candidate_id', '=', 'candidates.id')
                ->join('examiners', 'gs_results.examiner_id', '=', 'examiners.id')
                ->join('examiners_groups', 'gs_results.group_id', '=', 'examiners_groups.id')
                ->where('gs_results.candidate_id', $candidate_id)
                ->where('gs_results.station_id', $station_id)
                ->where('examiners.id', $examinerId)
                ->first();
        }

        // If no record is found in either table
        if (!$candidateResult) {
            return redirect()->back()->with('error', 'Candidate not found.');
        }

        // Decode `question_mark` if it exists
        if (isset($candidateResult->question_mark)) {
            $candidateResult->question_mark = json_decode($candidateResult->question_mark, true);
        }

        $data['candidate'] = $candidateResult;

        // Dynamically choose the view based on the source table
        if ($candidateResult->source_table === 'mcs_results') {
            // Pass the data with $getRecord as the variable to match the Blade template
            $data['getRecord'] = [$candidateResult];  // Wrap in array to maintain compatibility
            return view('examiner.resubmit', $data);
        } elseif ($candidateResult->source_table === 'gs_results') {
            // Similarly for gs_results
            $data['getRecord'] = [$candidateResult];  // Wrap in array
            return view('examiner.gsresubmit', $data);
        }

        // Fallback in case of unexpected conditions
        return redirect()->back()->with('error', 'Unable to determine the correct resubmit view.');
    }



    public function updateEvaluation(Request $request, $candidate_id, $station_id)
    {
        $loggedInUserId = Auth::id();
        $examiner = DB::table('examiners')->where('user_id', $loggedInUserId)->first();

        if (!$examiner) {
            return redirect()->back()->with('error', 'Examiner data not found.');
        }

        $examinerId = $examiner->id;

        // Fetch the record from `mcs_results` or `gs_results`
        $evaluation = DB::table('mcs_results')
            ->where('candidate_id', $candidate_id)
            ->where('station_id', $station_id)
            ->where('examiner_id', $examinerId)
            ->first();

        $sourceTable = 'mcs_results';

        if (!$evaluation) {
            $evaluation = DB::table('gs_results')
                ->where('candidate_id', $candidate_id)
                ->where('station_id', $station_id)
                ->where('examiner_id', $examinerId)
                ->first();

            $sourceTable = 'gs_results';
        }

        if (!$evaluation) {
            return redirect()->back()->with('error', 'Evaluation not found.');
        }

        // Prepare update data
        $updateData = [
            'group_id' => $request->group_id,
            'station_id' => $request->station_id,
            'question_mark' => json_encode($request->question_marks ?? []), // Ensure it's always an array
            'total' => $request->total_marks,
            'remarks' => $request->remarks,
        ];

        // Include `overall` only if the source table is `mcs_results`
        if ($sourceTable === 'mcs_results') {
            $updateData['overall'] = $request->grade;
        }

        // Update the respective source table
        DB::table($sourceTable)
            ->where('candidate_id', $candidate_id)
            ->where('station_id', $station_id)
            ->where('examiner_id', $examinerId)
            ->update($updateData);

        return redirect('examiner/results')->with('success', 'Evaluation updated successfully.');
    }
}
