<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\HospitalModel;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $data['header_title'] = 'Dashboard';

        if (Auth::user()->user_type == 1) {
            // Fetch the count of trainees
            $traineeCount = User::getTrainee()->count();
            $CandidateCount = User::getCandidates()->count();
            $accreditedHospitalCount = HospitalModel::where('status', 'active')->count();

            $data['traineeCount'] = $traineeCount;
            $data['accreditedHospitalCount'] = $accreditedHospitalCount;
            $data['CandidateCount'] = $CandidateCount;

            return view('admin.dashboard', $data);
         } elseif (Auth::user()->user_type == 2) {
            return view('trainee.dashboard', $data);
      }elseif (Auth::user()->user_type == 9) {
        return view('examiner.examiner_form', $data);
  }
    }

// Landing page for Examiner 

    public function examinerform()
{
      $examinerGroupId = \DB::table('examiners')
        ->where('user_id', Auth::id())
        ->value('group_id');

    // Fetch all groups from the examiners_groups table
    $groups = \DB::table('examiners_groups')->get();

    $data['header_title'] = 'Examiner Form';
    $data['getRecord'] = User::getexaminerCandidates();
    $data['groups'] = $groups; 
    $data['examinerGroupId'] = $examinerGroupId;

    return view('examiner.examiner_form', $data);
}
}
