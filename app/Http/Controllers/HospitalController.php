<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\HospitalModel;
use App\Models\Country;

class HospitalController extends Controller
{
    public function hospital(){

        $data['getRecord'] = HospitalModel::getRecord();
        $data ['header_title'] = 'Hospitals';
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


    //Add New Hospital View
    public function add(){
        $data['countries'] = Country::all();
        $data['header_title'] = "Add New Hospital";
        return view('admin.hospital.add_hospital',$data);
    }


    //Posting Hospital Data
    public function insert(Request $request){

        // dd($request-> all());
        $save = new HospitalModel;
        $save->name = trim($request->name);
        $save->country_id = $request->country_id;
        $save->status = $request->status;
        $save->save();
        return redirect('admin/hospital/list')->with('success',"Hospital successfully created");
    }

    //edit Hospital
    public function edit($id){

        $data['getRecord'] = HospitalModel::getSingleId($id);
        $data['countries'] = Country::all();
        if(!empty($data['getRecord'])){

            $data['header_title'] = "Edit Hospital";
            return view('admin.hospital.edit_hospital',$data);

        }else{

            abort(404);
        }

    }

       // Update Hospital
       public function update(Request $request, $id)
       {
           $update = HospitalModel::find($id);
           $update->name = trim($request->name);
           $update->country_id = $request->country_id;
           $update->status = $request->status;
           $update->save();
           return redirect('admin/hospital/list')->with('success', "Hospital successfully updated");
       }


       public function delete($id){

        $data = HospitalModel::getSingleId($id);
        $data->is_deleted = 1;
        $data->save();
        return redirect('admin/hospital/list')->with('success',"Information successfully Deleted");
      }

}


