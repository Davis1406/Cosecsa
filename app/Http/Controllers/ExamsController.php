<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ExaminersImport;
use App\Models\User;
use Auth;
use Hash;
use App\Models\Country;
use App\Models\ExamsModel;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class ExamsController extends Controller
{
    public function list()
    {
        $data ['header_title'] = 'Examiners';
        $data['getExaminers'] = User::getExaminers();        
        return view('admin.exams.examiners', $data);
    }
    
    public function import()
    {
        $data['header_title'] = "Import Examiners";
        return view('admin.exams.import', $data);
    }

    public function importExaminers(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');
        Excel::import(new ExaminersImport, $file);

        return redirect('admin/exams/examiners')->with('success', 'Examiners imported successfully');
    }

    public function add()
    {
        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Add New Examiner";  
        $data['groups'] = DB::table('examiners_groups')->select('id', 'group_name')->get();
        return view('admin.exams.add_examiner', $data);
    }
    
    public function insert(Request $request)
    {
        $userType = 9; 
    
        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'user_type' => $userType
        ]);
    
        // Create Examiner
        ExamsModel::create([
            'user_id' => $user->id,
            'email' => $request->email,
            'gender' => $request->gender,
            'examiner_id' => $request->examiner_id,
            'group_id' => $request->group_id,
            'country_id' => $request->country_id,
            'mobile' => $request->mobile,
            'shift' => $request->shift,
            'specialty' => $request->specialty,
        ]);
    
        return redirect('admin/exams/examiners/')->with('success', 'Examiner added successfully');
    }
public function view($id)
{
    // Get current year ID
    $currentYearId = User::getCurrentYearId();
    
    // Get examiner with current year filtering
    $examiner = User::getExaminers($currentYearId)->firstWhere('examin_id', $id);

    if (!$examiner) {
        return redirect('admin/exams/examiners')->with('error', 'Examiner not found');
    }

    // Get the base URL - using your IP address for mobile access
    $baseUrl = 'http://localhost/cosecsa';
    
    $confirmationUrl = $baseUrl . '/admin/exams/confirm-attendance/' . $examiner->examin_id;

    // Generate QR code with the confirmation URL
    $qrCode = \QrCode::size(70)->generate($confirmationUrl);

    $header_title = "View Examiner";

    return view('admin.exams.view_examiner', compact('examiner', 'header_title', 'qrCode'));
}

    /**
     * Show attendance confirmation page after QR scan
     */
    public function showAttendanceConfirmation($examiner_id)
    {
        $examiner = User::getExaminers()->firstWhere('examin_id', $examiner_id);

        if (!$examiner) {
            return redirect('admin/exams/examiners')->with('error', 'Examiner not found');
        }

        // Check if already registered today
        $existingAttendance = Attendance::where('examiner_id', $examiner->examin_id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        $data = [
            'header_title' => 'Confirm Attendance Registration',
            'examiner' => $examiner,
            'already_registered' => $existingAttendance ? true : false,
            'registration_time' => $existingAttendance ? $existingAttendance->created_at->format('H:i:s') : null
        ];

        return view('admin.exams.confirm_attendance', $data);
    }

    /**
     * Process attendance registration after confirmation
     */
    public function confirmAttendanceRegistration(Request $request, $examiner_id)
    {
        try {
            $examiner = User::getExaminers()->firstWhere('examin_id', $examiner_id);
            
            if (!$examiner) {
                return redirect()->back()->with('error', 'Examiner not found');
            }

            // Check for duplicate attendance (same examiner same day)
            $existingAttendance = Attendance::where('examiner_id', $examiner->examin_id)
                ->whereDate('created_at', Carbon::today())
                ->first();

            if ($existingAttendance) {
                return redirect()->back()->with('info', 
                    'Attendance already recorded for today at ' . $existingAttendance->created_at->format('H:i:s'));
            }

            // Create new attendance record
            $attendance = Attendance::create([
                'user_id'        => $examiner->user_id ?? null,
                'examiner_id'    => $examiner->examin_id ?? null,
                'country_id'     => $examiner->country_id ?? null,
                'group_id'       => $examiner->group_id ?? null,
                'mobile'         => $examiner->mobile ?? null,
                'specialty'      => $examiner->specialty ?? null,
                'subspecialty'   => $examiner->subspecialty ?? null,
                'shift'          => $examiner->shift ?? null,
                'curriculum_vitae' => $examiner->curriculum_vitae ?? null,
                'passport_image' => $examiner->passport_image ?? null,
            ]);

            return redirect()->back()->with('success', 
                'Attendance registered successfully for ' . $examiner->examiner_name . ' at ' . $attendance->created_at->format('H:i:s'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error registering attendance: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $examiner = User::getExaminers()->firstWhere('examin_id', $id);
        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Edit Examiners";
        $data['examiner'] = $examiner;
    
        // Retrieve all groups and pass them to the view
        $data['groups'] = DB::table('examiners_groups')->select('id', 'group_name')->get();
    
        return view('admin.exams.edit_examiner', $data);
    }
    
    public function update(Request $request, $id)
    {
        $examiner = ExamsModel::find($id);
        if (!$examiner) {
            return redirect('admin/exams/examiners')->with('error', 'Examiner not found');
        }
    
        $user = User::find($examiner->user_id);
    
        $user->name = $request->name;
        $user->email = $request->email;
        if (!empty($request->password)) {
            $user->password = $request->password;
        }
        $user->save();
    
        // Handle file uploads
        if ($request->hasFile('curriculum_vitae')) {
            $file = $request->file('curriculum_vitae');
            
            // Generate a unique filename for storage
            $uniqueName = uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Store the file (saves to storage/app/public/documents/cvs/)
            $path = $file->storeAs('documents/cvs', $uniqueName, 'public');
            
            // Save BOTH paths in the database
            $examiner->curriculum_vitae = $path;          // Unique storage path
            $examiner->curriculum_vitae = $file->getClientOriginalName(); // Original name
        }

        if ($request->hasFile('passport_image')) {
            $passportPath = $request->file('passport_image')->store('documents/passports', 'public');
            $examiner->passport_image = $passportPath;
        }
        
        $examiner->examiner_id = $request->examiner_id;
        $examiner->country_id = $request->country_id;
        $examiner->group_id = $request->group_id;
        $examiner->mobile = $request->mobile;
        $examiner->gender = $request->gender;
        $examiner->specialty = $request->specialty;
        $examiner->shift = $request->shift;
    
        $examiner->save();
    
        return redirect('admin/exams/examiners')->with('success', 'Examiner updated successfully');
    }

    public function delete($id)
    {
        $user = User::find($id);
  
        if (!$user) {
            return redirect('admin/exams/examiners')->with('error', 'User not found');
        }
  
        $examiner = ExamsModel::where('user_id', $user->id)->first();
  
        if (!$examiner) {
            return redirect('admin/exams/examiners')->with('error', 'Examiner not found');
        }
  
        if ($user->user_type != 9) {
            return redirect('admin/exams/examiners')->with('error', 'User is not an Examiner');
        }
  
        $user->is_deleted = 1;
  
        if ($user->save()) {
            return redirect('admin/exams/examiners')->with('success', 'Examimer successfully deleted');
        }
  
        return redirect('admin/exams/examiners')->with('error', 'Failed to delete examiners information');
    }

    public function adminResults()
    {
        $data['header_title'] = 'MCS Results';
        $getResults = User::getAdminExamsResults();
        $data['getResults'] = $getResults;
        return view('admin.exams.exam_results', $data);
    }

    public function gsResults()
    {
        $data['header_title'] = 'GS Results';
        $getResults = User::getGsResults();
        $data['getResults'] = $getResults;
        return view('admin.exams.gs_results', $data);
    }
   
    // Single Station Results
    public function viewCandidateStationResult($candidate_id, $station_id)
    {   
        $header_title = 'Station Results';
        $candidateResult = \DB::table('examination_form')
            ->join('candidates', 'examination_form.candidate_id', '=', 'candidates.id')
            ->join('examiners_groups', 'candidates.group_id', '=', 'examiners_groups.id')
            ->join('examiners', 'examiners.id', '=', 'examination_form.examiner_id')
            ->join('users', 'examiners.user_id', '=', 'users.id')
            ->select(
                'candidates.candidate_id as candidate_name',
                'examiners_groups.group_name',
                'examination_form.station_id',
                'examination_form.total',
                'examination_form.question_mark',
                'examination_form.overall',
                'examination_form.remarks',
                'examiners.examiner_id as examin_id',
                'users.name as examiner_name'
            )
            ->where('examination_form.candidate_id', $candidate_id)
            ->where('examination_form.station_id', $station_id)
            ->first();

        return view('admin.exams.station_results', compact('candidateResult', 'header_title'));
    }

    public function viewGsStationResult($candidate_id, $station_id)
    {
        $header_title = 'Station Results';

        // Fetch the primary candidate result
        $candidateResult = \DB::table('gs_form')
            ->join('candidates', 'gs_form.candidate_id', '=', 'candidates.id')
            ->join('examiners_groups', 'gs_form.group_id', '=', 'examiners_groups.id')
            ->join('examiners', 'gs_form.examiner_id', '=', 'examiners.id')
            ->join('users', 'examiners.user_id', '=', 'users.id')
            ->select(
                'candidates.candidate_id as candidate_name',
                'examiners_groups.group_name as g_name',
                'gs_form.station_id as s_id',
                'gs_form.total',
                'gs_form.question_mark',
                'gs_form.remarks',
                'examiners.examiner_id as examin_id',
                'users.name as examiner_name'
            )
            ->where('gs_form.candidate_id', $candidate_id)
            ->where('gs_form.station_id', $station_id)
            ->first();

        // Fetch all results for the station by all examiners
        $allResults = \DB::table('gs_form')
            ->join('examiners', 'gs_form.examiner_id', '=', 'examiners.id')
            ->join('examiners_groups', 'gs_form.group_id', '=', 'examiners_groups.id')
            ->join('users', 'examiners.user_id', '=', 'users.id')
            ->select(
                'gs_form.total',
                'gs_form.question_mark',
                'gs_form.station_id as s_id',
                'examiners_groups.group_name as g_name',
                'gs_form.remarks',
                'examiners.examiner_id',
                'users.name as examiner_name'
            )
            ->where('gs_form.candidate_id', $candidate_id)
            ->where('gs_form.station_id', $station_id)
            ->get();

        return view('admin.exams.gs_station_results', compact('candidateResult', 'allResults', 'header_title'));
    }

    // Function to change password
    public function changePassword()
    {
        $data['header_title'] = "Change Password";
        return view('examiner.change_password', $data);
    }

    public function updatePassword(Request $request)
    {
        $user = User::getSingleId(Auth::user()->id);
        if (Hash::check($request->old_password, $user->password)){
            $user->password = Hash::make($request->new_password);
            $user->save();
            return redirect()->back()->with('success', "Password successfully updated");
        }
        else{
            return redirect()->back()->with('error', "Old Password is not correct");
        }
    } 

    public function examinerProfile()
{
    $user = Auth::user();
    
    // Get examiner details using the same method as admin
    $examiner = User::getExaminers()->firstWhere('user_id', $user->id);
    
    if (!$examiner) {
        return redirect('examiner/dashboard')->with('error', 'Examiner profile not found');
    }

    // Generate QR code for the examiner using examiner-specific route
    // $baseUrl = request()->getSchemeAndHttpHost();
    $baseUrl = 'http://localhost/cosecsa';

    $confirmationUrl = $baseUrl . '/examiner/confirm-attendance/' . $examiner->examin_id;
    
    // Generate QR code with the confirmation URL
    $qrCode = \QrCode::size(70)->generate($confirmationUrl);

    $data = [
        'header_title' => 'Profile Settings',
        'examiner' => $examiner,
        'getCountry' => Country::getCountry(),
        'groups' => DB::table('examiners_groups')->select('id', 'group_name')->get(),
        'qrCode' => $qrCode // Add QR code to the data
    ];

    return view('examiner.profile_settings', $data);
}

// Add this new method for generating examiner badge
public function examinerBadge()
{
    $user = Auth::user();
    
    // Get examiner details
    $examiner = User::getExaminers()->firstWhere('user_id', $user->id);
    
    if (!$examiner) {
        return redirect('examiner/profile_settings')->with('error', 'Examiner profile not found');
    }

    // Generate QR code using examiner-specific route
    $baseUrl = request()->getSchemeAndHttpHost();
    $confirmationUrl = $baseUrl . '/examiner/confirm-attendance/' . $examiner->examin_id;
    $qrCode = \QrCode::size(70)->generate($confirmationUrl);

    $data = [
        'header_title' => 'ID Badge',
        'examiner' => $examiner,
        'qrCode' => $qrCode
    ];

    return view('examiner.badge', $data);
}

/**
 * Show attendance confirmation page after QR scan (Examiner-specific version)
 */
public function showExaminerAttendanceConfirmation($examiner_id)
{
    $examiner = User::getExaminers()->firstWhere('examin_id', $examiner_id);

    if (!$examiner) {
        return redirect('examiner/dashboard')->with('error', 'Examiner not found');
    }

    // Check if already registered today
    $existingAttendance = Attendance::where('examiner_id', $examiner->examin_id)
        ->whereDate('created_at', Carbon::today())
        ->first();

    $data = [
        'header_title' => 'Confirm Attendance Registration',
        'examiner' => $examiner,
        'already_registered' => $existingAttendance ? true : false,
        'registration_time' => $existingAttendance ? $existingAttendance->created_at->format('H:i:s') : null
    ];

    return view('examiner.confirm_attendance', $data);
}

/**
 * Process attendance registration after confirmation (Examiner-specific version)
 */
public function confirmExaminerAttendanceRegistration(Request $request, $examiner_id)
{
    try {
        $examiner = User::getExaminers()->firstWhere('examin_id', $examiner_id);
        
        if (!$examiner) {
            return redirect()->back()->with('error', 'Examiner not found');
        }

        // Check for duplicate attendance (same examiner same day)
        $existingAttendance = Attendance::where('examiner_id', $examiner->examin_id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        if ($existingAttendance) {
            return redirect()->back()->with('info', 
                'Attendance already recorded for today at ' . $existingAttendance->created_at->format('H:i:s'));
        }

        // Create new attendance record
        $attendance = Attendance::create([
            'user_id'        => $examiner->user_id ?? null,
            'examiner_id'    => $examiner->examin_id ?? null,
            'country_id'     => $examiner->country_id ?? null,
            'group_id'       => $examiner->group_id ?? null,
            'mobile'         => $examiner->mobile ?? null,
            'specialty'      => $examiner->specialty ?? null,
            'subspecialty'   => $examiner->subspecialty ?? null,
            'shift'          => $examiner->shift ?? null,
            'curriculum_vitae' => $examiner->curriculum_vitae ?? null,
            'passport_image' => $examiner->passport_image ?? null,
        ]);

        return redirect()->back()->with('success', 
            'Attendance registered successfully for ' . $examiner->examiner_name . ' at ' . $attendance->created_at->format('H:i:s'));

    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Error registering attendance: ' . $e->getMessage());
    }
}

public function updateExaminerProfile(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'country_id' => 'required|integer',
        'mobile' => 'nullable|string|max:20',
        'gender' => 'nullable|in:Male,Female',
        'specialty' => 'nullable|string|max:255',
        'shift' => 'nullable|in:Morning,Afternoon,Morning & Afternoon',
        'passport_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'curriculum_vitae' => 'nullable|mimes:pdf,doc,docx|max:10240',
        'password' => 'nullable|min:6'
    ]);

    $user = Auth::user();
    $examinerModel = ExamsModel::where('user_id', $user->id)->first();
    
    if (!$examinerModel) {
        return redirect()->back()->with('error', 'Examiner profile not found');
    }

    // Update user table
    $user->name = $request->name;
    $user->email = $request->email;
    
    // Only update password if provided
    if (!empty($request->password)) {
        $user->password = Hash::make($request->password);
    }
    $user->save();

    // Handle file uploads
    if ($request->hasFile('curriculum_vitae')) {
        // Delete old CV if exists
        if ($examinerModel->curriculum_vitae && Storage::disk('public')->exists($examinerModel->curriculum_vitae)) {
            Storage::disk('public')->delete($examinerModel->curriculum_vitae);
        }
        
        $file = $request->file('curriculum_vitae');
        // Generate a unique filename for storage
        $uniqueName = uniqid() . '_cv.' . $file->getClientOriginalExtension();
        // Store the file
        $path = $file->storeAs('documents/cvs', $uniqueName, 'public');
        // Save path in the database
        $examinerModel->curriculum_vitae = $path;
    }

    if ($request->hasFile('passport_image')) {
        // Delete old passport image if exists
        if ($examinerModel->passport_image && Storage::disk('public')->exists($examinerModel->passport_image)) {
            Storage::disk('public')->delete($examinerModel->passport_image);
        }
        
        $file = $request->file('passport_image');
        $uniqueName = uniqid() . '_passport.' . $file->getClientOriginalExtension();
        $passportPath = $file->storeAs('documents/passports', $uniqueName, 'public');
        $examinerModel->passport_image = $passportPath;
    }
    
    // Update examiner details
    $examinerModel->country_id = $request->country_id;
    $examinerModel->mobile = $request->mobile;
    $examinerModel->gender = $request->gender;
    $examinerModel->specialty = $request->specialty;
    $examinerModel->shift = $request->shift;
    $examinerModel->email = $request->email;
    
    $examinerModel->save();

    return redirect()->back()->with('success', 'Profile updated successfully');
}


public function examinerChangePassword(Request $request)
{
    $request->validate([
        'old_password' => 'required',
        'new_password' => 'required|min:6',
        'confirm_password' => 'required|same:new_password'
    ]);

    $user = Auth::user();
    
    if (!Hash::check($request->old_password, $user->password)) {
        return redirect()->back()->with('error', 'Old password is incorrect');
    }
    
    $user->password = Hash::make($request->new_password);
    $user->save();
    
    return redirect()->back()->with('success', 'Password updated successfully');
}

public function examinerEdit($id)
{
    // Get the current logged-in examiner
    $currentExaminer = Auth::user();
    
    // Security check: make sure examiner can only edit their own profile
    $examiner = User::getExaminers()->where('examin_id', $id)->first();
    
    if (!$examiner || $examiner->user_id != $currentExaminer->id) {
        return redirect('examiner/profile_settings')->with('error', 'Unauthorized access');
    }
    
    $data['getCountry'] = Country::getCountry();
    $data['header_title'] = "Edit Profile";
    $data['examiner'] = $examiner;
    
    // Retrieve all groups and pass them to the view
    $data['groups'] = DB::table('examiners_groups')->select('id', 'group_name')->get();
    
    return view('examiner.edit_info', $data);
}

public function examinerUpdate(Request $request, $id)
{
    // Get the current logged-in examiner
    $currentExaminer = Auth::user();
    
    // Find the examiner record
    $examiner = ExamsModel::find($id);

    if (!$examiner) {
        return redirect('examiner/profile_settings')->with('error', 'Examiner not found');
    }

    // Security check: make sure examiner can only update their own profile
    if ($examiner->user_id != $currentExaminer->id) {
        return redirect('examiner/profile_settings')->with('error', 'Unauthorized access');
    }

    // Validation rules
    $validated = $request->validate([
        // Step 1: Personal Information
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $currentExaminer->id,
        'password' => 'nullable|min:6',
        'gender' => 'nullable|in:Male,Female',
        'curriculum_vitae' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        'passport_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        
        // Step 2: Examiner Details
        'examiner_id' => 'nullable|string|max:255',
        'group_id' => 'nullable|integer',
        'specialty' => 'nullable|string|max:255',
        'subspecialty' => 'nullable|string|max:255',
        'country_id' => 'required|exists:countries,id',
        'mobile' => 'nullable|string|max:20',
        'exam_availability' => 'nullable|array',
        'exam_availability.*' => 'in:MCS,FCS',
        'shift' => 'nullable|in:1,2,3',
        
        // Step 3: Examiner History
        'virtual_mcs_participated' => 'nullable|in:Yes,No',
        'fcs_participated' => 'nullable|in:Yes,No',
        'participation_type' => 'nullable|in:Examiner,Observer',
        'hospital_type' => 'nullable|in:Teaching Hospital,Non Teaching',
        'hospital_name' => 'nullable|string|max:255',
        'examination_years' => 'nullable|array',
        'examination_years.*' => 'in:2020,2021,2022,2023,2024',
    ]);

    try {
        \DB::beginTransaction();

        // 1. Update user information (Step 1)
        $user = User::find($examiner->user_id);
        if ($user) {
            $user->name = $validated['name'];
            $user->email = $validated['email'];

            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            $user->save();
        }

        // 2. Handle file uploads (Step 1)
        if ($request->hasFile('curriculum_vitae')) {
            // Delete old CV if exists
            if ($examiner->curriculum_vitae && Storage::disk('public')->exists($examiner->curriculum_vitae)) {
                Storage::disk('public')->delete($examiner->curriculum_vitae);
            }
            
            $file = $request->file('curriculum_vitae');
            $uniqueName = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('documents/cvs', $uniqueName, 'public');
            $examiner->curriculum_vitae = $path;
        }

        if ($request->hasFile('passport_image')) {
            // Delete old image if exists
            if ($examiner->passport_image && Storage::disk('public')->exists($examiner->passport_image)) {
                Storage::disk('public')->delete($examiner->passport_image);
            }
            
            $passportPath = $request->file('passport_image')->store('documents/passports', 'public');
            $examiner->passport_image = $passportPath;
        }

        // 3. Update examiner information in examiners table
        $examiner->gender = $validated['gender'] ?? $examiner->gender;
        $examiner->examiner_id = $validated['examiner_id'] ?? $examiner->examiner_id;
        $examiner->country_id = $validated['country_id'];
        $examiner->mobile = $validated['mobile'] ?? $examiner->mobile;
        $examiner->specialty = $validated['specialty'] ?? $examiner->specialty;
        $examiner->subspecialty = $validated['subspecialty'] ?? $examiner->subspecialty;

        // Handle role_id based on participation_type
        if (isset($validated['participation_type'])) {
            if ($validated['participation_type'] === 'Examiner') {
                $examiner->role_id = 1;
            } elseif ($validated['participation_type'] === 'Observer') {
                $examiner->role_id = 2;
            }
        }

        // Save the examiner record to examiners table
        $examiner->save();

        // 4. Handle examiner history in separate table (examiners_history)
        $historyData = [];
        
        // Handle exam availability as JSON
        if ($request->has('exam_availability') && is_array($request->exam_availability)) {
            $historyData['exam_availability'] = json_encode($request->exam_availability);
        }
        
        if (isset($validated['virtual_mcs_participated'])) {
            $historyData['virtual_mcs_participated'] = $validated['virtual_mcs_participated'];
        }
        
        if (isset($validated['fcs_participated'])) {
            $historyData['fcs_participated'] = $validated['fcs_participated'];
        }
        
        if (isset($validated['hospital_type'])) {
            $historyData['hospital_type'] = $validated['hospital_type'];
        }
        
        if (isset($validated['hospital_name'])) {
            $historyData['hospital_name'] = $validated['hospital_name'];
        }
        
        if (isset($validated['examination_years'])) {
            $historyData['examination_years'] = json_encode($validated['examination_years']);
        }

        // Update or create history record
        if (!empty($historyData)) {
            \App\Models\ExaminerHistory::updateOrCreate(
                ['exm_id' => $examiner->id],
                $historyData
            );
        }

        // 5. Handle group assignment in exams_groups table
        if (isset($validated['group_id'])) {
            $currentYear = User::getCurrentYearId();
            
            // Remove existing group assignments for current year
            \DB::table('exams_groups')
                ->where('exm_id', $examiner->id)
                ->where('year_id', $currentYear)
                ->delete();
            
            // Add new group assignment
            \DB::table('exams_groups')->insert([
                'exm_id' => $examiner->id,
                'group_id' => $validated['group_id'],
                'year_id' => $currentYear,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 6. Handle shift assignment in exams_shifts table
        if (isset($validated['shift'])) {
            $currentYear = User::getCurrentYearId();
            
            // Remove existing shift assignments for current year
            \DB::table('exams_shifts')
                ->where('exm_id', $examiner->id)
                ->where('year_id', $currentYear)
                ->delete();
            
            // Add new shift assignment
            \DB::table('exams_shifts')->insert([
                'exm_id' => $examiner->id,
                'shift' => $validated['shift'],
                'year_id' => $currentYear,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        \DB::commit();

        return redirect('examiner/profile_settings')->with('success', 'Profile updated successfully');
        
    } catch (\Exception $e) {
        \DB::rollback();
        
        // Log the error for debugging
        \Log::error('Examiner update failed: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return redirect()->back()
            ->withInput()
            ->with('error', 'An error occurred while updating the profile. Please try again. Error: ' . $e->getMessage());
    }
}

}