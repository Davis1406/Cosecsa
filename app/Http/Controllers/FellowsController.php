<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\FellowsModel;
use App\Models\Country;
use App\Models\UserRole;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\FellowshipImport;

class FellowsController extends Controller
{
    public function list()
    {
        $data['header_title'] = 'Fellows';
        $data['getFellows'] = User::getFellows();        
        return view('admin.associates.fellows.list', $data);
    }

    public function view($id)
    {
        $fellow = User::getFellows()->firstWhere('fellow_id', $id);
        if (!$fellow) {
            return redirect('admin/associates/fellows/list')->with('error', 'Fellow not found');
        }
        $header_title = "View Fellow";
        return view('admin.associates.fellows.view', compact('fellow', 'header_title'));
    }

    public function add()
    {
        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Add New Fellow";  
        return view('admin.associates.fellows.add', $data);
    }

    public function insert(Request $request)
    {
        $fullName = trim("{$request->firstname} {$request->middlename} {$request->lastname}");

        $profileImagePath = null;
        if ($request->hasFile('profile_image')) {
            $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
        }

        $userType = 7; // Fellow

        // Create user
        $user = User::create([
            'name' => $fullName,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'user_type' => $userType
        ]);

        // Assign role in user_roles table
        UserRole::create([
            'user_id' => $user->id,
            'role_type' => $userType,
            'is_active' => 1
        ]);

        // Create fellow record
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

        return redirect('admin/associates/fellows/list')->with('success', 'Fellows imported successfully');
    }

    public function edit($id)
    {
        $fellow = User::getFellows()->firstWhere('fellow_id', $id);
        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Edit Fellow";
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

        $profileImagePath = $fellow->profile_image;
        if ($request->hasFile('profile_image')) {
            $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
        }

        $fullName = trim("{$request->firstname} {$request->middlename} {$request->lastname}");

        $user->name = $fullName;
        $user->email = $request->email;
        if (!empty($request->password)) {
            $user->password = bcrypt($request->password);
        }
        $user->save();

        // Ensure role is present in user_roles
        UserRole::firstOrCreate([
            'user_id' => $user->id,
            'role_type' => 7
        ], [
            'is_active' => 1
        ]);

        $fellow->update([
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
            'phone_number' => $request->phone_number
        ]);

        return redirect('admin/associates/fellows/list')->with('success', 'Fellow updated successfully');
    }
public function delete($id)
{
    $user = User::find($id);
    if (!$user) {
        return redirect('admin/associates/fellows/list')->with('error', 'User not found');
    }

    $fellow = FellowsModel::where('user_id', $user->id)->first();
    if (!$fellow) {
        return redirect('admin/associates/fellows/list')->with('error', 'Fellow not found');
    }

    if ($user->user_type != 7) {
        return redirect('admin/associates/fellows/list')->with('error', 'User is not a fellow');
    }

    // Soft deactivate roles
    \DB::table('user_roles')
        ->where('user_id', $user->id)
        ->where('role_type', 7) // Fellow role
        ->update([
            'is_active' => 0,
            'updated_at' => now()
        ]);

    return redirect('admin/associates/fellows/list')->with('success', 'Fellow successfully Deleted');
}


}
