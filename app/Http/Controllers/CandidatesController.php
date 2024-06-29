<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Trainee;
use App\Models\HospitalModel;
use App\Models\Programme;
use App\Models\Country;
use App\Models\Candidates;
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

    public function view($id)
    {
        $candidate = User::getCandidates()->firstWhere('candidate_id', $id);
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

        
        // Create trainee
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

}
