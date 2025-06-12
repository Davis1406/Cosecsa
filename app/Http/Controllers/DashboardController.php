<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\HospitalModel;
use App\Models\YearModel;
use App\Models\ExaminerGroup;

class DashboardController extends Controller
{
   

public function dashboard()
    {
        $data['header_title'] = 'Dashboard';

        if (Auth::user()->user_type == 1) {
            // Admin dashboard - same as before
            $traineeCount = User::getTrainee()->count();
            $CandidateCount = User::getCandidates()->count();
            $FellowsCount = User::getFellows()->count();
            $accreditedHospitalCount = HospitalModel::where('status', 'active')->count();

            $data['traineeCount'] = $traineeCount;
            $data['accreditedHospitalCount'] = $accreditedHospitalCount;
            $data['CandidateCount'] = $CandidateCount;
            $data['FellowsCount'] = $FellowsCount;

            return view('admin.dashboard', $data);
        } 
        elseif (Auth::user()->user_type == 2) {
            return view('trainee.dashboard', $data);
        }
        elseif (Auth::user()->user_type == 9) {
            return view('examiner.dashboard', $data);
        }
    }

    // Updated examiner form method
    public function examinerform()
    {
        $currentYear = YearModel::orderBy('id', 'desc')->first();
        $yearId = $currentYear ? $currentYear->id : null;

        // Get examiner's groups for current year
        $examinerGroups = User::getExaminerGroups(Auth::id(), $yearId);
        $examinerGroupIds = $examinerGroups->pluck('id')->toArray();

        // Get all available groups
        $allGroups = ExaminerGroup::all();

        // Get candidates for examiner's groups in current year
        $candidates = User::getExaminerCandidates(Auth::id(), $yearId);

        $data['header_title'] = 'Examiner Form';
        $data['getRecord'] = $candidates;
        $data['groups'] = $allGroups;
        $data['examinerGroups'] = $examinerGroups;
        $data['examinerGroupIds'] = $examinerGroupIds;
        $data['currentYear'] = $currentYear;

        return view('examiner.dashboard', $data);
    }
}
