<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\HospitalModel;
use App\Models\Country;
use DB;

class HospitalController extends Controller
{
    public function hospital(){

        $allHospitals = HospitalModel::select('hospitals.*', 'countries.country_name as country_name')
            ->join('countries', 'countries.id', 'hospitals.country_id')
            ->where('hospitals.is_deleted', 0)
            ->orderBy('countries.country_name')
            ->orderBy('hospitals.name')
            ->get();

        $data['getRecord'] = $allHospitals;
        $data['header_title'] = 'Hospitals';

        // Stats for visual report
        $data['totalHospitals']  = $allHospitals->count();
        $data['totalActive']     = $allHospitals->where('status', 0)->count();
        $data['totalInactive']   = $allHospitals->where('status', 1)->count();
        $data['countGovt']       = $allHospitals->where('hospital_type', 1)->count();
        $data['countNGO']        = $allHospitals->where('hospital_type', 2)->count();
        $data['countPrivate']    = $allHospitals->where('hospital_type', 3)->count();
        $data['countUniversity'] = $allHospitals->where('hospital_type', 4)->count();

        // By country (for bar chart) — sort by count desc, take top 20
        $data['byCountry'] = $allHospitals->groupBy('country_name')
            ->map(fn($g) => $g->count())
            ->sortDesc();

        // By type (for doughnut chart)
        $data['byType'] = $allHospitals->groupBy('hospital_type')
            ->map(fn($g) => $g->count());

        return view('admin.hospital.list', $data);
    }

    public function view($id) {
        $hospital = HospitalModel::select('hospitals.*', 'countries.country_name as country_name')
            ->join('countries', 'countries.id', 'hospitals.country_id')
            ->where('hospitals.id', $id)
            ->first();

        if ($hospital) {
            $header_title = 'Hospital Details';
            return view('admin.hospital.view_hospital', compact('hospital', 'header_title'));
        } else {
            return redirect('admin/hospital/list')->with('error', 'Hospital not found');
        }
    }

    public function add(){
        $data['countries'] = Country::whereIn('id', function($q){
            $q->select('country_id')->from('hospitals')->where('is_deleted', 0);
        })->orWhereIn('country_name', ['Kenya','Uganda','Tanzania','Ethiopia','Rwanda','Zambia','Zimbabwe',
            'Malawi','Namibia','Burundi','Sudan','Mozambique','Botswana','DRC','South Sudan',
            'Madagascar','Niger','Lesotho','Somaliland','Gabon','Eswatini','Togo','Cameroon','Angola'])
          ->orderBy('country_name')->get();
        $data['header_title'] = "Add New Hospital";
        return view('admin.hospital.add_hospital', $data);
    }

    public function insert(Request $request){
        $save = new HospitalModel;
        $save->name          = trim($request->name);
        $save->country_id    = $request->country_id;
        $save->hospital_type = $request->hospital_type ?? 1;
        $save->status        = $request->status;
        $save->save();
        return redirect('admin/hospital/list')->with('success', "Hospital successfully created");
    }

    public function edit($id){
        $data['getRecord'] = HospitalModel::getSingleId($id);
        $data['countries']  = Country::orderBy('country_name')->get();
        if(!empty($data['getRecord'])){
            $data['header_title'] = "Edit Hospital";
            return view('admin.hospital.edit_hospital', $data);
        } else {
            abort(404);
        }
    }

    public function update(Request $request, $id){
        $update = HospitalModel::find($id);
        $update->name          = trim($request->name);
        $update->country_id    = $request->country_id;
        $update->hospital_type = $request->hospital_type ?? $update->hospital_type;
        $update->status        = $request->status;
        $update->save();
        return redirect('admin/hospital/list')->with('success', "Hospital successfully updated");
    }

    public function delete($id){
        $data = HospitalModel::getSingleId($id);
        $data->is_deleted = 1;
        $data->save();
        return redirect('admin/hospital/list')->with('success', "Hospital successfully deleted");
    }
}
