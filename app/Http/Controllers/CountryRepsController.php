<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\HospitalModel;
use App\Models\Programme;
use App\Models\Country;
use App\Models\UserRole;
use App\Models\CountryRepsModel;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\RepsImport;

class CountryRepsController extends Controller
{
    public function list()
    {
        $data['getRecord'] = User::getreps();
        $data['header_title'] = "CR's List";
        return view('admin.associates.reps.list', $data);
    }

    public function view($id)
    {
        $countryRep = User::getReps()->firstWhere('reps_id', $id);
        if (!$countryRep) {
            return redirect('admin/associates/reps/list')->with('error', 'CR not found');
        }
        $header_title = "View CR";
        return view('admin.associates.reps.view', compact('countryRep', 'header_title'));
    }

    public function add()
    {

        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Add New CR's";
        return view('admin.associates.reps.add', $data);
    }

    public function import()
    {
        $data['header_title'] = "Import CR's";
        return view('admin.associates.reps.import', $data);
    }

    public function importData(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');
        Excel::import(new RepsImport, $file);

        return redirect('admin/associates/reps/list')->with('success', 'Reps imported successfully');
    }
    public function insert(Request $request)
    {
        // Handle profile image upload
        $profileImagePath = null;
        if ($request->hasFile('profile_image')) {
            $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
        }

        $userType = 4; // '4' represents trainers

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password), // Hash the password
            'user_type' => $userType
        ]);

        // Assign role in user_roles table
        UserRole::create([
            'user_id' => $user->id,
            'role_type' => $userType,
            'is_active' => 1
        ]);

        $repData = [
            'user_id' => $user->id,
            'country_id' => $request['country_id'],
            'profile_image' => $profileImagePath,
            'cosecsa_email' => $request['cosecsa_email'],
            'mobile_no' => $request['mobile_no'],
        ];

        CountryRepsModel::create($repData);

        return redirect('admin/associates/reps/list')->with('success', 'CR added successfully');
    }

    public function edit($id)
    {
        $countryRep = User::getReps()->firstWhere('reps_id', $id);
        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Edit Country Rep";
        $data['countryRep'] = $countryRep;
        return view('admin.associates.reps.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $countryRep = CountryRepsModel::find($id);
        if (!$countryRep) {
            return redirect('admin/associates/trainers/list')->with('error', 'Trainer not found');
        }

        $user = User::find($countryRep->user_id);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
        } else {
            $profileImagePath = $countryRep->profile_image; // keep the old image if no new one is uploaded
        }

        $user->name = $request->name;
        $user->email = $request->email;
        if (!empty($request->password)) {
            $user->password = bcrypt($request->password); // Hash the password
        }
        $user->save();

        // Update user role if needed (ensure it exists and is active)
        $userRole = UserRole::where('user_id', $user->id)
            ->where('role_type', $user->user_type)
            ->first();

        if (!$userRole) {
            // Create user role if it doesn't exist
            UserRole::create([
                'user_id' => $user->id,
                'role_type' => $user->user_type,
                'is_active' => 1
            ]);
        } else {
            // Ensure the role is active
            $userRole->is_active = 1;
            $userRole->save();
        }

        $countryRep->country_id = $request->country_id;
        $countryRep->profile_image = $profileImagePath;
        $countryRep->cosecsa_email = $request->cosecsa_email;
        $countryRep->mobile_no = $request->mobile_no;
        $countryRep->save();

        return redirect('admin/associates/reps/list')->with('success', 'CR updated successfully');
    }

    public function delete($id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect('admin/associates/reps/list')->with('error', 'User not found');
        }

        $trainer = CountryRepsModel::where('user_id', $user->id)->first();

        if (!$trainer) {
            return redirect('admin/associates/reps/list')->with('error', 'Country Rep not found');
        }

        if ($user->user_type != 5) {
            return redirect('admin/associates/reps/list')->with('error', 'User is not a CR');
        }

        // Soft delete via user_roles table
        \DB::table('user_roles')
            ->where('user_id', $user->id)
            ->where('role_type', 5) // CR role type
            ->update(['is_active' => 0]);

        return redirect('admin/associates/reps/list')->with('success', 'CR information successfully deleted');
    }
}
