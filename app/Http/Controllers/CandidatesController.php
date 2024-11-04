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
use Illuminate\Support\Facades\Storage;
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
        // Concatenate names
        $fullName = trim("{$request->firstname} {$request->middlename} {$request->lastname}");


        $userType = 3; //  '3' represents candidates

        // Create user
        $user = User::create([
            'name' => $fullName,
            'email' => $request->email,
            'password' =>($request->password),
            'user_type' => $userType
        ]);

        
        // Create Candidate
        $candidateData = [
            'user_id'=> $user->id,
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
            'repeat_P1'=> $request['repeat_P1'],
            'repeat_P2'=> $request['repeat_P2'],
            'mmed'=> $request['mmed'],
            'entry_number' => $request['entry_number'],
            'admission_year' => $request['admission_year'],
            'exam_year' => $request['exam_year'],
            'invoice_number' => $request['invoice_number'],
            'invoice_date' => $request['invoice_date'],
            'invoice_status' => $request['invoice_status'],
            'sponsor' => $request['sponsor'],
            'amount_paid' => $request['amount_paid'],
        ];


        Candidates::create($candidateData);

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

    // Update User
    $fullName = trim("{$request->firstname} {$request->middlename} {$request->lastname}");
    $user->name = $fullName;
    $user->email = $request->email;
    $user->password = $request->password; // This will trigger the setPasswordAttribute
    $user->save();

    // Handle profile image upload
    if ($request->hasFile('profile_image')) {
        $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
        $trainee->profile_image = $profileImagePath;
    }

    // Update Candidate
    $candidate->firstname = $request->firstname;
    $candidate->middlename = $request->middlename;
    $candidate->lastname = $request->lastname;
    $candidate->personal_email = $request->personal_email;
    $candidate->gender = $request->gender;
    $candidate->programme_id = $request->programme_id;
    $candidate->hospital_id = $request->hospital_id;
    $candidate->country_id = $request->country_id;
    $candidate->entry_number = $request->entry_number;
    $candidate->repeat_paper_one = $request->repeat_paper_one;
    $candidate->repeat_paper_two = $request->repeat_paper_two;
    $candidate->admission_year = $request->admission_year;
    $candidate->exam_year = $request->exam_year;
    $candidate->mmed = $request->mmed;
    $candidate->invoice_number = $request->invoice_number;
    $candidate->invoice_date = $request->invoice_date;
    $candidate->invoice_status = $request->invoice_status;
    $candidate->sponsor = $request->sponsor;
    // $candidate->mode_of_payment = $request->mode_of_payment;
    $candidate->amount_paid = $request->amount_paid;
    // $candidate->payment_date = $request->payment_date;

    $candidate->save();

    return redirect('admin/associates/candidates/list')->with('success', 'Candidate updated successfully');
}

public function delete($id)
{
    $user = User::find($id);

    if (!$user) {
        return redirect('admin/associates/candidates/list')->with('error', 'User not found');
    }

    $candidate = Candidates::where('user_id', $user->id)->first();

    if (!$candidate) {
        return redirect('admin/associates/candidates/list')->with('error', 'Candidates not found');
    }
    if ($user->user_type != 2 && $user->user_type != 3) {
        return redirect('admin/associates/candidates/list')->with('error', 'User is not a trainee or candidate');
    }

    $user->is_deleted = 1;
    $user->save();

    return redirect('admin/associates/candidates/list')->with('success', 'Candidate information successfully deleted');
}


public function getCandidatesByGroup($groupId)
{
    $candidates = User::getCandidatesByGroup($groupId);
    return response()->json($candidates);
} 


public function storeEvaluation(Request $request)
{
    // Get the logged-in user's ID
    $loggedInUserId = Auth::id();

    $examiner = \DB::table('examiners')->where('user_id', $loggedInUserId)->first();

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
    $grade = strtolower($request->input('grade'));
    $evaluation->remarks = $request->input('remarks');

    // dd($evaluation);
    
    $evaluation->save();

    return redirect()->back()->with('success', 'Evaluation submitted successfully.');
}


//Candidates List for examiners
public function results()
{
    $data['getRecord'] = User::getExaminationResults();
    $data['header_title'] = "Candidates Results";
    return view('examiner.results', $data);

}

public function viewCandidateResults($candidate_id)
{
    $data['header_title'] = "View Candidate";

    $data['candidateResult'] = \DB::table('examination_form')
        ->select(
            'examination_form.*',
            'candidates.id as candidate_id',
            'candidates.candidate_id as candidate_name',
            'candidates.group_id as g_id',
            'examiners.id as examiner_id',
            'examiners_groups.group_name as group_name'
        )
        ->join('candidates', 'examination_form.candidate_id', '=', 'candidates.id')
        ->join('examiners', 'examination_form.examiner_id', '=', 'examiners.id')
        ->join('examiners_groups', 'candidates.group_id', '=', 'examiners_groups.id')
        ->where('candidates.id', $candidate_id)
        ->first();

    return view('examiner.view_results', $data);
}


public function resubmit($candidate_id)
{
    $data['header_title'] = "Resubmit Results";

    $data['candidate'] = \DB::table('examination_form')
        ->select(
            'examination_form.*',
            'candidates.id as candidates_id',
            'candidates.candidate_id as candidate_name',
            'candidates.group_id as g_id',
            'examiners.id as examiner_id',
            'examiners_groups.group_name as group_name'
        )
        ->join('candidates', 'examination_form.candidate_id', '=', 'candidates.id')
        ->join('examiners', 'examination_form.examiner_id', '=', 'examiners.id')
        ->join('examiners_groups', 'candidates.group_id', '=', 'examiners_groups.id')
        ->where('candidates.id', $candidate_id)
        ->first();

    if (!$data['candidate']) {
        return redirect()->back()->with('error', 'Candidate not found.');
    }

    if (isset($data['candidate']->question_mark)) {
        $data['candidate']->question_mark = json_decode($data['candidate']->question_mark) ?? [];
    } else {
        $data['candidate']->question_mark = []; 
    }

        return view('examiner.resubmit', $data);
}


public function updateEvaluation(Request $request, $id)
{
 
    // Find the candidate evaluation entry
    $evaluation = CandidatesFormModel::where('candidate_id', $id)->first();
    if (!$evaluation) {
        return redirect()->back()->with('error', 'Candidate evaluation not found');
    }

    // Update evaluation fields
    $evaluation->group_id = $request->group_id;
    $evaluation->station_id = $request->station_id;
    $evaluation->question_mark = $request->question_marks;
    $evaluation->total = $request->total_marks;
    $evaluation->overall = $request->grade;
    $evaluation->remarks = $request->remarks;

    $evaluation->save();

    return redirect('examiner/results')->with('success', 'Evaluation updated successfully');

}




}
