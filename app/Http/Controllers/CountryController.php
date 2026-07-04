<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use Illuminate\Support\Facades\DB;

class CountryController extends Controller
{
    public function list()
    {
        $countries = DB::table('countries')
            ->select('countries.*',
                DB::raw('(SELECT COUNT(*) FROM hospitals WHERE hospitals.country_id = countries.id AND hospitals.is_deleted = 0) as hospital_count'),
                DB::raw('(SELECT COUNT(*) FROM trainees WHERE trainees.country_id = countries.id) as trainee_count'),
                DB::raw('(SELECT COUNT(*) FROM fellows WHERE fellows.country_id = countries.id) as fellow_count'),
                DB::raw('(SELECT COUNT(*) FROM members WHERE members.country_id = countries.id) as member_count')
            )
            ->orderBy('countries.country_name')
            ->get();

        return view('admin.countries.list', [
            'countries'    => $countries,
            'header_title' => 'Countries',
        ]);
    }

    public function view($id)
    {
        $country = Country::find($id);
        if (!$country) {
            return redirect('admin/countries/list')->with('error', 'Country not found');
        }

        // Hospitals in this country
        $hospitals = DB::table('hospitals')
            ->where('hospitals.country_id', $id)
            ->where('hospitals.is_deleted', 0)
            ->select('hospitals.id', 'hospitals.name', 'hospitals.hospital_type', 'hospitals.status')
            ->orderBy('hospitals.name')
            ->get();

        // Trainees from this country
        $trainees = DB::table('trainees')
            ->join('users', 'users.id', '=', 'trainees.user_id')
            ->join('programmes', 'programmes.id', '=', 'trainees.programme_id')
            ->leftJoin('hospitals', 'hospitals.id', '=', 'trainees.hospital_id')
            ->where('trainees.country_id', $id)
            ->where('users.is_deleted', 0)
            ->select('trainees.id as trainee_id', 'users.name', 'users.email',
                     'programmes.name as programme_name', 'programmes.id as programme_id',
                     'hospitals.name as hospital_name', 'hospitals.id as hospital_id',
                     'trainees.admission_year', 'trainees.status')
            ->orderBy('users.name')
            ->get();

        // Fellows from this country
        $fellows = DB::table('fellows')
            ->join('users', 'users.id', '=', 'fellows.user_id')
            ->leftJoin('programmes', 'programmes.id', '=', 'fellows.programme_id')
            ->where('fellows.country_id', $id)
            ->where('users.is_deleted', 0)
            ->select('fellows.id as fellow_id', 'users.name', 'users.email',
                     'programmes.name as programme_name', 'programmes.id as programme_id',
                     'fellows.fellowship_year', 'fellows.status')
            ->orderBy('users.name')
            ->get();

        // Members from this country
        $members = DB::table('members')
            ->join('users', 'users.id', '=', 'members.user_id')
            ->where('members.country_id', $id)
            ->where('users.is_deleted', 0)
            ->select('members.id as member_id', 'users.name', 'users.email', 'members.status')
            ->orderBy('users.name')
            ->get();

        // Country reps from this country
        $reps = DB::table('country_reps')
            ->join('users', 'users.id', '=', 'country_reps.user_id')
            ->where('country_reps.country_id', $id)
            ->where('users.is_deleted', 0)
            ->select('country_reps.id as rep_id', 'users.name', 'users.email', 'country_reps.status')
            ->orderBy('users.name')
            ->get();

        // Trainers at hospitals in this country
        $trainers = DB::table('trainers')
            ->join('users', 'users.id', '=', 'trainers.user_id')
            ->join('hospitals', 'hospitals.id', '=', 'trainers.hospital_id')
            ->join('user_roles', function($j){
                $j->on('user_roles.user_id','=','users.id')
                  ->where('user_roles.role_type', 4)
                  ->where('user_roles.is_active', 1);
            })
            ->where('hospitals.country_id', $id)
            ->where('hospitals.is_deleted', 0)
            ->where('users.is_deleted', 0)
            ->select('trainers.id as trainer_id', 'users.name', 'users.email',
                     'hospitals.name as hospital_name', 'hospitals.id as hospital_id')
            ->orderBy('users.name')
            ->get();

        // Examiners from this country
        $examiners = DB::table('examiners')
            ->join('users', 'users.id', '=', 'examiners.user_id')
            ->leftJoin('examiners_roles', 'examiners_roles.id', '=', 'examiners.role_id')
            ->where('examiners.country_id', $id)
            ->where('users.is_deleted', 0)
            ->select('examiners.id as examiner_id', 'users.id as user_id', 'users.name', 'users.email',
                     'examiners_roles.role as examiner_role')
            ->orderBy('users.name')
            ->get();

        return view('admin.countries.view', compact(
            'country', 'hospitals', 'trainees', 'fellows',
            'members', 'reps', 'trainers', 'examiners'
        ) + ['header_title' => $country->country_name]);
    }
}
