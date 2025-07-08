<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserRole;
use App\Models\HospitalModel;
use App\Models\YearModel;
use App\Models\ExaminerGroup;

class DashboardController extends Controller
{
   

public function dashboard()
    {
        $data['header_title'] = 'Dashboard';
        
        $activeRole = Auth::user()->getActiveRole();
        $data['userRoles'] = Auth::user()->getRoles();
        $data['activeRole'] = $activeRole;

        switch ($activeRole) {
            case 1:
                // Admin dashboard logic
                $traineeCount = User::getTrainee()->count();
                $CandidateCount = User::getCandidates()->count();
                $FellowsCount = User::getFellows()->count();
                $accreditedHospitalCount = HospitalModel::where('status', 'active')->count();
                
                $data['traineeCount'] = $traineeCount;
                $data['accreditedHospitalCount'] = $accreditedHospitalCount;
                $data['CandidateCount'] = $CandidateCount;
                $data['FellowsCount'] = $FellowsCount;
                
                return view('admin.dashboard', $data);
                
            case 2:
                return view('trainee.dashboard', $data);
                
            case 7:
                return view('fellow.dashboard', $data);
                
            case 9:
                return view('examiner.dashboard', $data);
                
            default:
                Auth::logout();
                return redirect('login')->with('error', 'Invalid role');
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
