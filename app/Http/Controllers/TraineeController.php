<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Trainee;
use App\Models\HospitalModel;
use App\Models\Programme;
use App\Models\Country;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
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
    public function view($id)
    {
        $trainee = User::getTrainee()->firstWhere('trainee_id', $id);
        if (!$trainee) {
            return redirect('admin/associates/trainees/trainees')->with('error', 'Trainee not found');
        }
        $header_title = "View Trainee";
        return view('admin.associates.trainees.view', compact('trainee', 'header_title'));

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
            'training_year' => $request->training_year,
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

    public function edit($id)
{
    $trainee = User::getTrainee()->firstWhere('trainee_id', $id);
    if (!$trainee) {
        return redirect('admin/associates/trainees/trainees')->with('error', 'Trainee not found');
    }
    $data['getHospital']   = HospitalModel::getHospital();
    $data['getProgramme']  = Programme::getProgramme();
    $data['getCountry']    = Country::getCountry();
    $data['getStudyYear']  = DB::table('study_year')->orderBy('programme_id')->orderBy('id')->get();
    $data['header_title']  = "Edit Trainee";
    $data['trainee']       = $trainee;
    return view('admin.associates.trainees.edit_trainee', $data);
}

public function update(Request $request, $id)
{
    $trainee = Trainee::find($id);
    if (!$trainee) {
        return redirect('admin/associates/trainees/trainees')->with('error', 'Trainee not found');
    }

    $user = User::find($trainee->user_id);

    // Update User
    $fullName = trim("{$request->firstname} {$request->middlename} {$request->lastname}");
    $user->name  = $fullName;
    $user->email = $request->email;
    if ($request->filled('password')) {
        $user->password = $request->password;
    }
    $user->save();

    // Handle profile image upload
    if ($request->hasFile('profile_image')) {
        $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
        $trainee->profile_image = $profileImagePath;
    }

    // Update Trainee
    $trainee->firstname               = $request->firstname;
    $trainee->middlename              = $request->middlename;
    $trainee->lastname                = $request->lastname;
    $trainee->personal_email          = $request->personal_email;
    $trainee->gender                  = $request->gender;
    $trainee->status                  = $request->status;
    $trainee->programme_id            = $request->programme_id;
    $trainee->hospital_id             = $request->hospital_id;
    $trainee->country_id              = $request->country_id;
    $trainee->entry_number            = $request->entry_number;
    $trainee->admission_letter_status = $request->admission_letter_status;
    $trainee->invitation_letter_status= $request->invitation_letter_status;
    $trainee->admission_year          = $request->admission_year ?: null;
    $trainee->exam_year               = $request->exam_year ?: 0;
    $trainee->training_year           = $request->training_year ?: null;
    $trainee->programme_period        = $request->programme_period;
    $trainee->invoice_number          = $request->invoice_number;
    $trainee->invoice_date            = $request->invoice_date ?: null;
    $trainee->invoice_status          = $request->invoice_status;
    $trainee->sponsor                 = $request->sponsor;
    $trainee->mode_of_payment         = $request->mode_of_payment ?? '';
    $trainee->amount_paid             = $request->amount_paid ?? 0;
    $trainee->payment_date            = $request->payment_date ?: null;
    $trainee->save();

    return redirect('admin/associates/trainees/trainees')->with('success', 'Trainee updated successfully');
}

    public function reports()
    {
        return view('admin.associates.trainees.reports', ['header_title' => 'Trainees Analytics']);
    }

    public function reportsData()
    {
        // KPIs
        $total  = DB::table('trainees')->join('users','users.id','=','trainees.user_id')->where('users.user_type',2)->count();
        $active = DB::table('trainees')->join('users','users.id','=','trainees.user_id')->where('users.user_type',2)->where('trainees.status','Active')->count();
        $male   = DB::table('trainees')->join('users','users.id','=','trainees.user_id')->where('users.user_type',2)->where('trainees.gender','Male')->count();
        $female = DB::table('trainees')->join('users','users.id','=','trainees.user_id')->where('users.user_type',2)->where('trainees.gender','Female')->count();

        // By Country (top 15)
        $byCountry = DB::table('trainees')
            ->join('users','users.id','=','trainees.user_id')
            ->join('countries','countries.id','=','trainees.country_id')
            ->where('users.user_type',2)
            ->selectRaw('countries.country_name as label, count(*) as value')
            ->groupBy('countries.country_name')
            ->orderByDesc('value')->limit(15)->get();

        // By Programme
        $byProgramme = DB::table('trainees')
            ->join('users','users.id','=','trainees.user_id')
            ->join('programmes','programmes.id','=','trainees.programme_id')
            ->where('users.user_type',2)
            ->selectRaw('programmes.name as label, count(*) as value')
            ->groupBy('programmes.name')->orderByDesc('value')->get();

        // By Status
        $byStatus = DB::table('trainees')
            ->join('users','users.id','=','trainees.user_id')
            ->where('users.user_type',2)
            ->selectRaw('COALESCE(status,"Unknown") as label, count(*) as value')
            ->groupBy('status')->orderByDesc('value')->get();

        // By Gender
        $byGender = DB::table('trainees')
            ->join('users','users.id','=','trainees.user_id')
            ->where('users.user_type',2)
            ->selectRaw('COALESCE(gender,"Unknown") as label, count(*) as value')
            ->groupBy('gender')->get();

        // By Admission Year trend (2015+)
        $byYear = DB::table('trainees')
            ->join('users','users.id','=','trainees.user_id')
            ->where('users.user_type',2)
            ->whereNotNull('trainees.admission_year')
            ->where('trainees.admission_year','>=',2015)
            ->where('trainees.admission_year','<=',2026)
            ->selectRaw('admission_year as label, count(*) as value')
            ->groupBy('admission_year')->orderBy('admission_year')->get();

        // By Study Year
        $byStudyYear = DB::table('trainees')
            ->join('users','users.id','=','trainees.user_id')
            ->join('study_year','study_year.id','=','trainees.training_year')
            ->where('users.user_type',2)
            ->selectRaw('study_year.name as label, count(*) as value')
            ->groupBy('study_year.name')->orderByDesc('value')->limit(12)->get();

        // Invoice/Payment status
        $byInvoice = DB::table('trainees')
            ->join('users','users.id','=','trainees.user_id')
            ->where('users.user_type',2)
            ->selectRaw('COALESCE(invoice_status,"Pending") as label, count(*) as value')
            ->groupBy('invoice_status')->get();

        // Country summary table (top 20)
        $countryTable = DB::table('trainees')
            ->join('users','users.id','=','trainees.user_id')
            ->join('countries','countries.id','=','trainees.country_id')
            ->where('users.user_type',2)
            ->selectRaw('
                countries.country_name,
                count(*) as total,
                sum(case when trainees.gender="Male"   then 1 else 0 end) as male,
                sum(case when trainees.gender="Female" then 1 else 0 end) as female,
                sum(case when trainees.status="Active" then 1 else 0 end) as active
            ')
            ->groupBy('countries.country_name')
            ->orderByDesc('total')->limit(20)->get();

        return response()->json(compact(
            'total','active','male','female',
            'byCountry','byProgramme','byStatus','byGender',
            'byYear','byStudyYear','byInvoice','countryTable'
        ));
    }

    public function delete($id)
{
    $user = User::find($id);

    if (!$user) {
        return redirect('admin/associates/trainees/trainees')->with('error', 'User not found');
    }

    $trainee = Trainee::where('user_id', $user->id)->first();

    if (!$trainee) {
        return redirect('admin/associates/trainees/trainees')->with('error', 'Trainee not found');
    }
    if ($user->user_type != 2) {
        return redirect('admin/associates/trainees/trainees')->with('error', 'User is not a trainee');
    }
    $user->is_deleted = 1;
    $user->save();

    return redirect('admin/associates/trainees/trainees')->with('success', 'Trainee information successfully deleted');
}



}
