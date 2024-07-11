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
        }
    }
}
