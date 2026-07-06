<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Programme;
use App\Models\HospitalModel;
use Illuminate\Support\Facades\DB;

class ProgrammesController extends Controller
{
    // List all programmes
    public function list()
    {
        $data['getRecord'] = Programme::getRecord();
        $data ['header_title'] = 'Programmes';
        return view('admin.programmes.list', $data);
    }

      //Add New Programme View
      public function add(){
        $data['getHospital'] = HospitalModel::getHospital();
        $data['getProgramme'] = Programme::getProgramme();
        $data['header_title'] = "Add New Programme";
        return view('admin.programmes.add_programmes',$data);
    }


    //Posting Hospital Data
    public function insert(Request $request){

        // dd($request-> all());
        $save = new Programme;
        $save->name = trim($request->name);
        $save->programme_type = $request->programme_type;
        $save->duration = $request->duration;
        $save->entry_fee = $request->entry_fee;
        $save->exam_fee = $request->exam_fee;
        $save->repeat_fee = $request->repeat_fee;
        $save->save();
        return redirect('admin/programmes/list')->with('success',"Programme successfully created");
    }

    // Show edit programme form
    public function edit($id)
    {
        $data['getRecord'] = Programme::getSingleId($id);
        if(!empty($data['getRecord'])){

            $data['header_title'] = "Edit Hospital";
            return view('admin.programmes.edit_programmes',$data);
            
        }else{

            abort(404);
        }
    }

    // Update programme data
       public function update(Request $request, $id)
       {
            // dd($request)->all();
           $update = Programme::find($id);
           $update->name = trim($request->name);
           $update->programme_type = $request->programme_type;
           $update->duration = $request->duration;
           $update->entry_fee = $request->entry_fee;
           $update->exam_fee = $request->exam_fee;
           $update->repeat_fee = $request->repeat_fee;
           $update->save();
           return redirect('admin/programmes/list')->with('success', "Programme successfully updated");
       }



    public function view($id)
    {
        $programme = Programme::getSingleId($id);
        if (!$programme || $programme->is_deleted) {
            return redirect('admin/programmes/list')->with('error', 'Programme not found');
        }

        // Accredited hospitals for this programme
        $hospitals = DB::table('hospital_programmes')
            ->join('hospitals', 'hospitals.id', '=', 'hospital_programmes.hospital_id')
            ->leftJoin('countries', 'countries.id', '=', 'hospitals.country_id')
            ->where('hospital_programmes.programme_id', $id)
            ->where('hospital_programmes.is_delete', 0)
            ->where('hospitals.is_deleted', 0)
            ->select('hospital_programmes.*',
                     'hospitals.id as hospital_id', 'hospitals.name as hospital_name',
                     'countries.country_name')
            ->orderBy('hospitals.name')
            ->get();

        // Trainees enrolled in this programme
        $trainees = DB::table('trainees')
            ->join('users', 'users.id', '=', 'trainees.user_id')
            ->leftJoin('hospitals', 'hospitals.id', '=', 'trainees.hospital_id')
            ->where('trainees.programme_id', $id)
            ->where('users.is_deleted', 0)
            ->select('trainees.id as trainee_id', 'users.name', 'users.email',
                     'hospitals.name as hospital_name', 'hospitals.id as hospital_id',
                     'trainees.admission_year', 'trainees.status')
            ->orderBy('users.name')
            ->get();

        // Fellows who completed this programme
        $fellows = DB::table('fellows')
            ->join('users', 'users.id', '=', 'fellows.user_id')
            ->where('fellows.programme_id', $id)
            ->where('users.is_deleted', 0)
            ->select('fellows.id as fellow_id', 'users.name', 'users.email',
                     'fellows.fellowship_year', 'fellows.status', 'fellows.country_id')
            ->leftJoin('countries', 'countries.id', '=', 'fellows.country_id')
            ->addSelect('countries.country_name')
            ->orderBy('users.name')
            ->get();

        // Capsule exam results for this programme (by year)
        $examResultsByYear = \DB::table('capsule_exam_results')
            ->where('programme_id', $id)
            ->selectRaw("exam_year, result, COUNT(*) as n")
            ->groupBy('exam_year', 'result')
            ->orderByDesc('exam_year')
            ->get()
            ->groupBy('exam_year');

        $examResultsAll = \DB::table('capsule_exam_results as cer')
            ->where('cer.programme_id', $id)
            ->leftJoin('trainees as t', 't.id', '=', 'cer.trainee_id')
            ->leftJoin('users as u', 'u.id', '=', 't.user_id')
            ->select('cer.contact_name', 'cer.exam_year', 'cer.exam_type',
                     'cer.score', 'cer.result', 'cer.trainee_id', 'u.name as trainee_name')
            ->orderByDesc('cer.exam_year')
            ->orderBy('cer.contact_name')
            ->get();

        $data = compact('programme', 'hospitals', 'trainees', 'fellows',
                        'examResultsByYear', 'examResultsAll');
        $data['header_title'] = $programme->name;
        return view('admin.programmes.view', $data);
    }

       public function delete($id){
      
        $data = Programme::getSingleId($id);
        $data->is_deleted = 1;
        $data->save();
        return redirect('admin/programmes/list')->with('success',"Information successfully Deleted");
      }
}
