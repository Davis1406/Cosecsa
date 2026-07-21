<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function changePassword(){
        $data['header_title'] = "Profile Settings";
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
            'signature_image' => 'nullable|image|max:2048',
        ]);

        $user = User::getSingleId(Auth::user()->id);
        $user->signature_title = $request->signature_title;
        $user->signature_phone = $request->signature_phone;

        if ($request->hasFile('signature_image')) {
            if ($user->signature_image_path && Storage::disk('public')->exists($user->signature_image_path)) {
                Storage::disk('public')->delete($user->signature_image_path);
            }
            $user->signature_image_path = $request->file('signature_image')->storeAs(
                'signatures', 'user-' . $user->id . '-' . time() . '.' . $request->file('signature_image')->getClientOriginalExtension(), 'public'
            );
        }

        $user->save();

        return redirect()->back()->with('success', "Email signature updated");
    }

    public function updateProfile(Request $request){
        $request->validate([
            'name'  => 'required|string|max:255',
            'bio'   => 'nullable|string|max:2000',
        ]);

        $user = User::getSingleId(Auth::user()->id);
        $user->name = trim($request->name);
        $user->bio  = $request->bio;

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $file      = $request->file('profile_image');
            $sanitized = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $finalName = 'user-' . $user->id . '-' . $sanitized . '.' . $file->getClientOriginalExtension();
            $user->profile_image = $file->storeAs('profile_images/users', $finalName, 'public');
        }

        $user->save();

        return redirect()->back()->with('success', "Profile updated");
    }
}
