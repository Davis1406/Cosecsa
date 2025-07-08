<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Trainer;
use App\Models\UserRole;
use App\Models\HospitalModel;
use App\Models\Programme;
use App\Models\Country;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TrainersImport;

class TrainerController extends Controller
{
    public function list()
    {
        $data['getRecord'] = User::getTrainers();
        $data['header_title'] = "Trainers List";
        return view('admin.associates.trainers.list', $data);
    }

    public function view($id)
    {
        $trainer = User::getTrainers()->firstWhere('trainer_id', $id);
        if (!$trainer) {
            return redirect('admin/associates/trainers/list')->with('error', 'Trainer not found');
        }
        $header_title = "View Trainer";
        return view('admin.associates.trainers.view', compact('trainer', 'header_title'));
    }

    public function add()
    {
        $data['getHospital'] = HospitalModel::getHospital();
        $data['getProgramme'] = Programme::getProgramme();
        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Add New Trainer";
        return view('admin.associates.trainers.add', $data);
    }

    public function import()
    {
        $data['header_title'] = "Import Trainers";
        return view('admin.associates.trainers.import', $data);
    }

    public function importData(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');
        Excel::import(new TrainersImport, $file);

        return redirect('admin/associates/trainers/list')->with('success', 'Trainers imported successfully');
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

        $trainerData = [
            'user_id' => $user->id,
            'phone_number' => $request['phone_number'],
            'hospital_id' => $request['hospital_id'],
            'profile_image' => $profileImagePath,
            'assistant_pd' => $request['assistant_pd'],
            'assistant_email' => $request['assistant_email'],
            'mobile_no' => $request['mobile_no'],
        ];

        Trainer::create($trainerData);

        return redirect('admin/associates/trainers/list')->with('success', 'Trainer added successfully');
    }
    public function edit($id)
    {
        $trainer = User::getTrainers()->firstWhere('trainer_id', $id);
        $data['getHospital'] = HospitalModel::getHospital();
        $data['getProgramme'] = Programme::getProgramme();
        $data['getCountry'] = Country::getCountry();
        $data['header_title'] = "Edit Trainer";
        $data['trainer'] = $trainer;
        return view('admin.associates.trainers.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $trainer = Trainer::find($id);
        if (!$trainer) {
            return redirect('admin/associates/trainers/list')->with('error', 'Trainer not found');
        }

        $user = User::find($trainer->user_id);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
        } else {
            $profileImagePath = $trainer->profile_image; // keep the old image if no new one is uploaded
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

        $trainer->phone_number = $request->phone_number;
        $trainer->hospital_id = $request->hospital_id;
        $trainer->profile_image = $profileImagePath;
        $trainer->assistant_pd = $request->assistant_pd;
        $trainer->assistant_email = $request->assistant_email;
        $trainer->mobile_no = $request->mobile_no;

        $trainer->save();

        return redirect('admin/associates/trainers/list')->with('success', 'Trainer updated successfully');
    }

    public function delete($id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect('admin/associates/trainers/list')->with('error', 'User not found');
        }

        $trainer = Trainer::where('user_id', $user->id)->first();

        if (!$trainer) {
            return redirect('admin/associates/trainers/list')->with('error', 'Trainer not found');
        }

        if ($user->user_type != 4) {
            return redirect('admin/associates/trainers/list')->with('error', 'User is not a trainer');
        }

        $user->is_deleted = 1;
        $user->save();

        return redirect('admin/associates/trainers/list')->with('success', 'Trainer information successfully deleted');
    }
}
