<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Trainee;
use App\Models\HospitalModel;
use App\Models\Programme;
use App\Models\Country;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TraineesImport;

class TraineeController extends Controller
{
    public function list()
    {
        $data['getRecord'] = User::getTrainee();
        $data['header_title'] = "Trainees List";
        return view('admin.associates.trainees.trainees', $data);
    }

    public function add()
    {
        $data['getHospital'] = HospitalModel::getHospital();
        $data['getProgramme'] = Programme::getProgramme();
        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Add New Trainee";  
        return view('admin.associates.trainees.add', $data);
    }

    public function import()
    {
    
        $data['header_title'] = "Import Trainees";
        return view('admin.associates.trainees.import', $data);
    }

    public function importData(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');
        Excel::import(new TraineesImport, $file);

        return redirect('admin/associates/trainees/trainees')->with('success', 'Trainees imported successfully');
    }

    public function insert(Request $request)
    {
        // Concatenate names
        $fullName = trim("{$request->firstname} {$request->middlename} {$request->lastname}");

        // Handle profile image upload
        $profileImagePath = null;
        if ($request->hasFile('profile_image')) {
            $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
        }

        $userType = 2; // Assuming '2' represents trainees

        // Create user
        $user = User::create([
            'name' => $fullName,
            'email' => $request->email,
            'password' =>($request->password),
            'user_type' => $userType
        ]);

        // Create trainee
        Trainee::create([
            'user_id' => $user->id,
            'firstname' => $request->firstname,
            'middlename' => $request->middlename,
            'lastname' => $request->lastname,
            'personal_email' => $request->personal_email,
            'gender' => $request->gender,
            'status' => $request->status,
            'profile_image' => $profileImagePath,
            'programme_id' => $request->programme_id,
            'hospital_id' => $request->hospital_id,
            'country_id' => $request->country_id,
            'entry_number' => $request->entry_number,
            'admission_letter_status' => $request->admission_letter_status,
            'invitation_letter_status' => $request->invitation_letter_status,
            'admission_year' => $request->admission_year,
            'exam_year' => $request->exam_year,
            'programme_period' => $request->programme_period,
            'invoice_number' => $request->invoice_number,
            'invoice_date' => $request->invoice_date,
            'invoice_status' => $request->invoice_status,
            'sponsor' => $request->sponsor,
            'mode_of_payment' => $request->mode_of_payment,
            'amount_paid' => $request->amount_paid,
            'payment_date' => $request->payment_date,
        ]);

        return redirect('admin/associates/trainees/trainees')->with('success', 'Trainee added successfully');
    }
}
