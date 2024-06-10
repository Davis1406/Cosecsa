<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Hash;

class AdminController extends Controller
{
    public function list(){

        $data['getRecord'] =User::getAdmin();
        $data['header_title'] = "Admin List";
        return view('admin.list',$data);
    }

    public function add(){
      
        $data['header_title'] = "Add New Admin";
        return view('admin.add',$data);
    }

    public function insert(Request $request){


        request()->validate([
            'email' => 'required|email|unique:users'
        ]);
        $user = new User;
        $user->name = trim($request->name);
        $user->email = trim($request->email);
        $user->password = Hash::make($request->password);
        $user->user_type = 1;
        $user->save();

        return redirect('admin/list')->with('success',"admin successfully created");
    }

    public function edit($id){
      
        $data['getRecord'] = User::getSingleId($id);
        if(!empty($data['getRecord'])){

            $data['header_title'] = "Edit Admin";
            return view('admin.edit',$data);
            
        }else{

            abort(404);
        }
    
    }


    public function update($id,Request $request){

        request()->validate([
            'email' => 'required|email|unique:users,email,'.$id
        ]);
        $user = User::getSingleId($id);
        $user->name = trim($request->name);
        $user->email = trim($request->email);
        $user->user_type = 1;
     
        if(!empty($request->password))
        {

            $user->password = Hash::make($request->password);
        }
        $user->save();

        return redirect('admin/list')->with('success',"Information successfully updated");
    }

    public function delete($id){
      
        $user = User::getSingleId($id);
        $user->is_deleted = 1;
        $user->save();
        return redirect('admin/list')->with('success',"Information successfully Deleted");

    }
}
