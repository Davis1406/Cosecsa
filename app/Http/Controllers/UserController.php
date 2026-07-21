<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Hash;

class UserController extends Controller
{
    public function changePassword(){
        $data['header_title'] = "Change Password";
        $data['user'] = User::getSingleId(Auth::user()->id);
        return view('profile.change_password', $data);
    }

    public function updatePassword(Request $request){
        $user = User::getSingleId(Auth::user()->id);
        if (Hash::check($request->old_password, $user->password)){

            $user->password = $request->new_password; // mutator bcrypts for admin (user_type=1)
            $user->save();
            return redirect()->back()->with('success', "Password successfully updated");
        }
        else{
            return redirect()->back()->with('error', "Old Password is not correct");
        }
    }

    public function updateSignature(Request $request){
        $request->validate([
            'signature_title' => 'nullable|string|max:255',
            'signature_phone' => 'nullable|string|max:50',
        ]);

        $user = User::getSingleId(Auth::user()->id);
        $user->signature_title = $request->signature_title;
        $user->signature_phone = $request->signature_phone;
        $user->save();

        return redirect()->back()->with('success', "Email signature updated");
    }
}
