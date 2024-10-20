<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\FellowsModel;
use App\Models\Country;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\FellowshipImport;

class FellowsController extends Controller
{
    public function list()
    {
        $data ['header_title'] = 'Fellows';
        $data['getFellows'] = User::getFellows();        
        return view('admin.associates.fellows.list', $data);
    }

    public function view($id)
    {
        $fellow = User::getFellows()->firstWhere('fellow_id', $id);
        if (!$fellow) {
            return redirect('admin/associates/fellows/list')->with('error', 'Fellows not found');
        }
        $header_title = "View Fellow";
        return view('admin.associates.fellows.view', compact('fellow', 'header_title'));

        dd($member);
    }

    public function add()
    {
        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Add New Fellow";  
        return view('admin.associates.fellows.add', $data);
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
    
        $userType = 7; // 7 for Fellows
    
        // Create user
        $user = User::create([
            'name' => $fullName,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'user_type' => $userType
        ]);
    
        // Create Fellow
        FellowsModel::create([
            'user_id' => $user->id,
            'firstname' => $request->firstname,
            'middlename' => $request->middlename,
            'lastname' => $request->lastname,
            'personal_email' => $request->personal_email,
            'gender' => $request->gender,
            'status' => $request->status,
            'address' => $request->address,
            'country_id' => $request->country_id,
            'programme_id' => $request->programme_id,
            'category_id' => $request->category_id,
            'organization' => $request->organization,
            'profile_image' => $profileImagePath,
            'admission_year' => $request->admission_year,
            'fellowship_year' => $request->fellowship_year,
            'current_specialty' => $request->current_specialty,
            'phone_number' => $request->phone_number,
        ]);
    
        return redirect('admin/associates/fellows/list')->with('success', 'Fellow added successfully');
    }
    

    public function import()
    {
    
        $data['header_title'] = "Import Fellows";
        return view('admin.associates.fellows.import_fellows', $data);
    }


    public function importFellows(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');
        Excel::import(new FellowshipImport, $file);

        return redirect('admin/associates/fellows/list')->with('success', 'Trainees imported successfully');
    }


    public function edit($id)
    {
        $fellow = User::getFellows()->firstWhere('fellow_id', $id);
        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Edit Fellows";
        $data['fellow'] = $fellow;
        return view('admin.associates.fellows.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $fellow = FellowsModel::find($id);
        if (!$fellow) {
            return redirect('admin/associates/fellows/list')->with('error', 'Fellow not found');
        }
    
        $user = User::find($fellow->user_id);
    
        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
        } else {
            $profileImagePath = $fellow->profile_image; // keep the old image if no new one is uploaded
        }
    
        $fullName = trim("{$request->firstname} {$request->middlename} {$request->lastname}");

        $user->name = $fullName;
        $user->email = $request->email;
        if (!empty($request->password)) {
            $user->password = $request->password;
        }
        $user->save();
    
        $fellow->firstname = $request->firstname;
        $fellow->middlename = $request->middlename;
        $fellow->lastname = $request->lastname;
        $fellow->personal_email = $request->personal_email;
        $fellow->gender = $request->gender;
        $fellow->status = $request->status;
        $fellow->address = $request->address;
        $fellow->country_id = $request->country_id;
        $fellow->programme_id = $request->programme_id;
        $fellow->category_id = $request->category_id;
        $fellow->organization = $request->organization;
        $fellow->profile_image = $profileImagePath;
        $fellow->admission_year = $request->admission_year;
        $fellow->fellowship_year = $request->fellowship_year;
        $fellow->current_specialty = $request->current_specialty;
        $fellow->phone_number = $request->phone_number;

        $fellow->save();
    
        return redirect('admin/associates/fellows/list')->with('success', 'Fellow updated successfully');
    }

    public function delete($id)
  {
    // Find the user by ID
    $user = User::find($id);

    // Check if user exists
    if (!$user) {
        return redirect('admin/associates/fellows/list')->with('error', 'User not found');
    }

    // Retrieve the associated member using the user_id
    $fellow = FellowsModel::where('user_id', $user->id)->first();

    // Check if fellow exists
    if (!$fellow) {
        return redirect('admin/associates/fellows/list')->with('error', 'Member not found');
    }

    // Verify that the user is of type 'member' (user_type 7)
    if ($user->user_type != 7) {
        return redirect('admin/associates/fellows/list')->with('error', 'User is not a member');
    }

    // Update the is_deleted status to 1 (mark as deleted/inactive)
    $user->is_deleted = 1;

    // Save the changes to the user
    if ($user->save()) {
        return redirect('admin/associates/fellows/list')->with('success', 'Member information successfully deleted');
    }

    // Return an error message if the save operation fails
    return redirect('admin/associates/fellows/list')->with('error', 'Failed to delete member information');
 }
}
