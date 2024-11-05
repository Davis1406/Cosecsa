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
use Illuminate\Support\Facades\DB;


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
        $examiner = User::getExaminers()->firstWhere('examin_id', $id);
        if (!$examiner) {
            return redirect('admin/exams/examiners')->with('error', 'Examiner not found');
        }
        $header_title = "View Examiner";
        return view('admin.exams.view_examiner', compact('examiner', 'header_title'));
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
      // Find the user by ID
      $user = User::find($id);
  
      // Check if user exists
      if (!$user) {
          return redirect('admin/exams/examiners')->with('error', 'User not found');
      }
  
      // Retrieve the associated member using the user_id
      $examiner = ExamsModel::where('user_id', $user->id)->first();
  
      // Check if fellow exists
      if (!$examiner) {
          return redirect('admin/exams/examiners')->with('error', 'Examiner not found');
      }
  
      // Verify that the user is of type 'examiner' (user_type 9)
      if ($user->user_type != 9) {
          return redirect('admin/exams/examiners')->with('error', 'User is not an Examiner');
      }
  
      // Update the is_deleted status to 1 (mark as deleted/inactive)
      $user->is_deleted = 1;
  
      // Save the changes to the user
      if ($user->save()) {
          return redirect('admin/exams/examiners')->with('success', 'Examimer successfully deleted');
      }
  
      // Return an error message if the save operation fails
      return redirect('admin/exams/examiners')->with('error', 'Failed to delete examiners information');
   }



   public function changePassword(){
    $data['header_title'] = "Change Password";
    return view('examiner.change_password', $data);
}

public function updatePassword(Request $request){
    
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

}
