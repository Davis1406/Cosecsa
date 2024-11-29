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
        ->join('examiners', 'examiners.id', '=', 'examination_form.examiner_id') // Join with examiners table
        ->join('users', 'examiners.user_id', '=', 'users.id') // Join with users table
        ->select(
            'candidates.candidate_id as candidate_name',
            'examiners_groups.group_name',
            'examination_form.station_id',
            'examination_form.total',
            'examination_form.question_mark',
            'examination_form.overall',
            'examination_form.remarks',
            'examiners.examiner_id as examin_id',
            'users.name as examiner_name' // Retrieve examiner's name from users table
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


//function to change password:

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
