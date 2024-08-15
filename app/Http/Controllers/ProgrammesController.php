<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Programme;
use App\Models\HospitalModel;

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



       public function delete($id){
      
        $data = Programme::getSingleId($id);
        $data->is_deleted = 1;
        $data->save();
        return redirect('admin/programmes/list')->with('success',"Information successfully Deleted");
      }
}
