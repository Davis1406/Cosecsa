<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Country;
use App\Models\MembersModel;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MembersImport;

class MembersController extends Controller
{
    public function list()
    {
        $data ['header_title'] = 'Members';
        $data['getMembers'] = User::getMembers();        
        return view('admin.associates.members.list', $data);
    }

    public function import()
    {
    
        $data['header_title'] = "Import members";
        return view('admin.associates.members.import_members', $data);
    }


    public function view($id)
    {
        $member = User::getMembers()->firstWhere('members_id', $id);
        if (!$member) {
            return redirect('admin/associates/members/list')->with('error', 'Member not found');
        }
        $header_title = "View Member";
        return view('admin.associates.members.view', compact('member', 'header_title'));

        // dd($member);
    }

    public function importMembers(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');
        Excel::import(new MembersImport, $file);

        return redirect('admin/associates/members/list')->with('success', 'Members imported successfully');
    }


    public function add()
    {
        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Add New Member";  
        return view('admin.associates.members.add', $data);
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

        $userType = 8; //8 For Members

        // Create user
        $user = User::create([
            'name' => $fullName,
            'email' => $request->email,
            'password' =>($request->password),
            'user_type' => $userType
        ]);

        // Create Member
        MembersModel::create([
            'user_id' => $user->id,
            'firstname' => $request->firstname,
            'middlename' => $request->middlename,
            'lastname' => $request->lastname,
            'password' => $request->password,
            'category_id' => $request->category_id,
            'personal_email' => $request->personal_email,
            'gender' => $request->gender,
            'status' => $request->status,
            'profile_image' => $profileImagePath,
            'country_id' => $request->country_id,
            'admission_year' => $request->admission_year,
            'membership_year' => $request->membership_year,
        ]);

        return redirect('admin/associates/members/list')->with('success', 'Members added successfully');
    }

    public function edit($id)
    {
        $member = User::getMembers()->firstWhere('members_id', $id);
        $data['getCountry'] = Country::getCountry();
        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Edit Members";
        $data['member'] = $member;
        return view('admin.associates.members.edit', $data);
    }


    public function update(Request $request, $id)
    {
        $member = MembersModel::find($id);
        if (!$member) {
            return redirect('admin/associates/members/list')->with('error', 'Member not found');
        }
    
        $user = User::find($member->user_id);
    
        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
        } else {
            $profileImagePath = $member->profile_image; // keep the old image if no new one is uploaded
        }
    
        $fullName = trim("{$request->firstname} {$request->middlename} {$request->lastname}");

        $user->name = $fullName;
        // $user->email = $request->email;
        if (!empty($request->password)) {
            $user->password = $request->password;
        }
        $user->save();
    
        $member->firstname = $request->firstname;
        $member->middlename = $request->middlename;
        $member->lastname = $request->lastname;
        $member->personal_email = $request->personal_email;
        $member->gender = $request->gender;
        $member->status = $request->status;
        $member->category_id = $request->category_id;
        $member->country_id = $request->country_id;
        $member->profile_image = $profileImagePath;
        $member->admission_year = $request->admission_year;
        $member->membership_year = $request->membership_year;
        $member->phone_number = $request->phone_number;

        $member->save();
    
        return redirect('admin/associates/members/list')->with('success', 'Member updated successfully');
    }


public function delete($id)
{
    // Find the user by ID
    $user = User::find($id);

    // Check if user exists
    if (!$user) {
        return redirect('admin/associates/members/list')->with('error', 'User not found');
    }

    // Retrieve the associated member using the user_id
    $member = MembersModel::where('user_id', $user->id)->first();

    // Check if member exists
    if (!$member) {
        return redirect('admin/associates/members/list')->with('error', 'Member not found');
    }

    // Verify that the user is of type 'member' (user_type 8)
    if ($user->user_type != 8) {
        return redirect('admin/associates/members/list')->with('error', 'User is not a member');
    }

    // Update the is_deleted status to 1 (mark as deleted/inactive)
    $user->is_deleted = 1;

    // Save the changes to the user
    if ($user->save()) {
        return redirect('admin/associates/members/list')->with('success', 'Member information successfully deleted');
    }

    // Return an error message if the save operation fails
    return redirect('admin/associates/members/list')->with('error', 'Failed to delete member information');
}

}
