<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlumniController extends Controller
{
    /** Return base query for alumni (fellows with a fellowship_year) */
    private function baseQuery()
    {
        return DB::table('fellows')
            ->join('users',          'users.id',      '=', 'fellows.user_id')
            ->leftJoin('programmes', 'programmes.id', '=', 'fellows.programme_id')
            ->leftJoin('countries',  'countries.id',  '=', 'fellows.country_id')
            ->leftJoin('categories', 'categories.id', '=', 'fellows.category_id')
            ->where('fellows.is_alumni', 1);
    }

    public function list()
    {
        $alumni = $this->baseQuery()
            ->select(
                'fellows.id as fellow_id',
                'fellows.user_id as f_id',
                'users.name as fellow_name',
                'fellows.personal_email',
                'fellows.gender',
                'fellows.fellowship_year',
                'fellows.current_specialty',
                'countries.country_name',
                'programmes.name as programme_name',
                'categories.category_name as fellowship_type',
                'fellows.candidate_number',
                'fellows.fcs_certificate_number',
                'fellows.status'
            )
            ->orderBy('fellows.fellowship_year', 'desc')
            ->orderBy('users.name')
            ->get();

        return view('admin.associates.alumni.list', [
            'alumni'       => $alumni,
            'header_title' => 'Alumni',
        ]);
    }

    public function reports()
    {
        return view('admin.associates.alumni.reports', ['header_title' => 'Alumni Analytics']);
    }

    public function reportsData()
    {
        $base = fn() => $this->baseQuery();

        $total   = $base()->count();
        $male    = $base()->where('fellows.gender', 'Male')->count();
        $female  = $base()->where('fellows.gender', 'Female')->count();
        $recent  = $base()->where('fellows.fellowship_year', date('Y'))->count();

        $byCountry = $base()
            ->selectRaw('countries.country_name as label, count(*) as value')
            ->groupBy('countries.country_name')
            ->orderByDesc('value')->limit(15)->get();

        $byProgramme = $base()
            ->selectRaw('COALESCE(programmes.name, "Unknown") as label, count(*) as value')
            ->groupBy('programmes.name')
            ->orderByDesc('value')->get();

        $byType = $base()
            ->selectRaw('COALESCE(categories.category_name, "Unknown") as label, count(*) as value')
            ->groupBy('categories.category_name')
            ->orderByDesc('value')->get();

        $byGender = $base()
            ->selectRaw('COALESCE(fellows.gender, "Unknown") as label, count(*) as value')
            ->groupBy('fellows.gender')->get();

        $byYear = $base()
            ->whereRaw('fellows.fellowship_year REGEXP "^[0-9]{4}$"')
            ->where('fellows.fellowship_year', '>=', 2004)
            ->selectRaw('fellows.fellowship_year as label, count(*) as value')
            ->groupBy('fellows.fellowship_year')
            ->orderBy('fellows.fellowship_year')->get();

        $bySpecialty = $base()
            ->whereNotNull('fellows.current_specialty')
            ->where('fellows.current_specialty', '!=', '')
            ->selectRaw('fellows.current_specialty as label, count(*) as value')
            ->groupBy('fellows.current_specialty')
            ->orderByDesc('value')->limit(10)->get();

        $countryTable = $base()
            ->selectRaw('
                countries.country_name,
                count(*) as total,
                sum(case when fellows.gender="Male"   then 1 else 0 end) as male,
                sum(case when fellows.gender="Female" then 1 else 0 end) as female,
                min(fellows.fellowship_year) as first_year,
                max(fellows.fellowship_year) as last_year
            ')
            ->groupBy('countries.country_name')
            ->orderByDesc('total')->limit(20)->get();

        return response()->json(compact(
            'total', 'male', 'female', 'recent',
            'byCountry', 'byProgramme', 'byType', 'byGender',
            'byYear', 'bySpecialty', 'countryTable'
        ));
    }
}
