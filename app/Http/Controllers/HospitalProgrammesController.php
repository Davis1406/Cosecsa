<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HospitalProgrammesModel;
use App\Models\HospitalModel;
use App\Models\Programme;

class HospitalProgrammesController extends Controller
{
    public function list(Request $request){
        $data['getHospitalProgrammes'] = HospitalProgrammesModel::getHospitalProgrammes($request);
        $data['header_title'] = "Hospital Programmes";
        return view('admin.hospitalprogrammes.list', $data);
    }

    public function add(){
        $data['getHospital'] = HospitalModel::getHospital();
        $data['getProgramme'] = Programme::getProgramme();
        $data['header_title'] = "Add New Programme";
        return view('admin.hospitalprogrammes.add', $data);
    }

    public function insert(Request $request){

        foreach ($request->programme_id as $programme_id) {
            // Check if the combination of hospital_id and programme_id already exists
            $exists = HospitalProgrammesModel::exists($request->hospital_id, $programme_id);

            if (!$exists) {
                // If it does not exist, create a new record
                HospitalProgrammesModel::create([
                    'hospital_id' => $request->hospital_id,
                    'programme_id' => $programme_id,
                    'status' => $request->status,
                ]);
            }
        }

        return redirect('admin/hospitalprogrammes/list')->with("success", "Programmes successfully assigned to hospital");
    }


    // public function edit($id) {
    //     $data['hospitalProgramme'] = HospitalProgrammesModel::find($id);
    //     $data['getHospital'] = HospitalModel::getHospital();
    //     $data['getProgramme'] = Programme::getProgramme();
    //     $data['header_title'] = "Edit Programme";
    //     return view('admin.hospitalprogrammes.edit', $data);
    // }

    public function edit($id) {
        $hospitalProgramme = HospitalProgrammesModel::find($id);
        $assignedProgrammes = HospitalProgrammesModel::where('hospital_id', $hospitalProgramme->hospital_id)
                                                    ->pluck('programme_id')
                                                    ->toArray();
    
        $data['hospitalProgramme'] = $hospitalProgramme;
        $data['assignedProgrammes'] = $assignedProgrammes;
        $data['getHospital'] = HospitalModel::getHospital();
        $data['getProgramme'] = Programme::getProgramme();
        $data['header_title'] = "Edit Programme";
        return view('admin.hospitalprogrammes.edit', $data);
    }
    
          

    public function update(Request $request, $id) {

        foreach ($request->programme_id as $programme_id) {

          $hospitalProgramme = HospitalProgrammesModel::find($id);

          $exists = HospitalProgrammesModel::exists($request->hospital_id, $programme_id);

          if ($hospitalProgramme) {

               if (!$exists) {
                // If it does not exist, create a new record
                HospitalProgrammesModel::create([
                    'hospital_id' => $request->hospital_id,
                    'programme_id' => $programme_id,
                ]);

             }

             else if ($exists) {
                                  // If it does not exist, create a new record
           
                    $exists->status = $request->status;
                    $exists->accredited_date=$request->accredited_date;
                    $exists->expiry_date= $request->expiry_date;
                    $exists->save();
                
                // dd($exists);

 }
    
              return redirect('admin/hospitalprogrammes/list')->with('success', 'Programme successfully updated');

          } 
            else {
              return redirect('admin/hospitalprogrammes/list')->with('error', 'Programme not found');
             }
       }
    //  dd($request-> all());
   }
    

    public function delete($id){
      
        $data = HospitalProgrammesModel::getSingleId($id);
        $data->is_delete = 1;
        $data->save();
        return redirect('admin/hospitalprogrammes/list')->with('success',"Information successfully Deleted");
      }


}

  