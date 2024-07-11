<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HospitalProgrammesModel;
use App\Models\HospitalModel;
use App\Models\Programme;
use App\Imports\HospitalProgrammesImport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

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
        $hospital_id = $request->input('hospital_id');
        $programme_ids = $request->input('programme_id', []);
        $accredited_date = $this->convertDateFormat($request->input('accredited_date'));
        $expiry_date = $this->convertDateFormat($request->input('expiry_date'));
        $status = $request->input('status');
    
        $existingProgrammes = [];
        $newProgrammes = [];
    
        foreach ($programme_ids as $programme_id) {
            // Check if the combination of hospital_id and programme_id already exists
            $exists = HospitalProgrammesModel::where('hospital_id', $hospital_id)
                                              ->where('programme_id', $programme_id)
                                              ->exists();
    
            if ($exists) {
                // Collect existing programme ids
                $existingProgrammes[] = $programme_id;
            } else {
                // Collect new programme ids for insertion
                $newProgrammes[] = [
                    'hospital_id' => $hospital_id,
                    'programme_id' => $programme_id,
                    'accredited_date' => $accredited_date,
                    'expiry_date' => $expiry_date,
                    'status' => $status
                ];
            }
        }
        // If there are existing programmes, redirect with an error message
        if (!empty($existingProgrammes)) {
            $existingProgrammeNames = Programme::whereIn('id', $existingProgrammes)->pluck('name')->toArray();
            $errorMessage = 'The following programmes already exist for the selected hospital: ' . implode(', ', $existingProgrammeNames);
            return redirect('admin/hospitalprogrammes/list')->with('error', $errorMessage);
        }
    
        // Insert new programmes
        HospitalProgrammesModel::insert($newProgrammes);
    
        return redirect('admin/hospitalprogrammes/list')->with("success", "Programmes successfully assigned to hospital");
    }

    public function import()
    {
        $data['header_title'] = "Import Hospital Programmes";
        return view('admin.hospitalprogrammes.import', $data);
    }

    public function importData(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');
        Excel::import(new HospitalProgrammesImport, $file);

        return redirect('admin/hospitalprogrammes/list')->with('success', 'Hospital Programmes imported successfully');
    }

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
        $hospitalProgramme = HospitalProgrammesModel::find($id);
    
        if (!$hospitalProgramme) {
            return redirect('admin/hospitalprogrammes/list')->with('error', 'Programme not found');
        }
    
        // Ensure programme_id is an array
        $programmeIds = $request->input('programme_id', []);
    
        if (!is_array($programmeIds)) {
            return redirect('admin/hospitalprogrammes/list')->with('error', 'Invalid programme data');
        }
    
        // Process each programme_id submitted in the form
        foreach ($programmeIds as $programme_id) {
            $accredited_date = $this->convertDateFormat($request->accredited_date);
            $expiry_date = $this->convertDateFormat($request->expiry_date);
    
            // Check if the programme_id exists for the hospital
            $exists = HospitalProgrammesModel::where('hospital_id', $hospitalProgramme->hospital_id)
                                              ->where('programme_id', $programme_id)
                                              ->first();
    
            if ($exists) {
                // Update existing record
                $exists->update([
                    'accredited_date' => $accredited_date,
                    'expiry_date' => $expiry_date,
                    'status' => $request->status
                ]);
            } else {
                // Create new record
                HospitalProgrammesModel::create([
                    'hospital_id' => $hospitalProgramme->hospital_id,
                    'programme_id' => $programme_id,
                    'accredited_date' => $accredited_date,
                    'expiry_date' => $expiry_date,
                    'status' => $request->status
                ]);
            }
        }
    
        return redirect('admin/hospitalprogrammes/list')->with('success', 'Programme successfully updated');
    }

    public function delete($id){
        $data = HospitalProgrammesModel::find($id);
        if ($data) {
            $data->delete();
        }
        return redirect('admin/hospitalprogrammes/list')->with('success',"Information successfully Deleted");
    }

    private function convertDateFormat($date)
    {
        if ($date) {
            return Carbon::createFromFormat('Y-m', $date)->format('Y-m-d');
        }
        return null;
    }
}
