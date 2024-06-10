<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use Hash;
use Auth;
use App\Models\User;
use App\Mail\ForgetPasswordMail;
Use Str;
Use Mail;

class AuthController extends Controller
{
    public function login()
    {
        if (!empty(Auth::check())){

            if (Auth::user()->user_type==1){

                return redirect('admin/dashboard');
            }

            if (Auth::user()->user_type==2){

                return redirect('trainee/dashboard');
            }
        }
        // dd(Hash::make('admin@2023'));
          return view('auth.login');
        
    }
          public function AuthLogin(Request $request)
          
          {
            $remember =!empty($request->remember) ? true : false;

            if (Auth::attempt(['email' => $request->email, 'password' => $request-> password], true)){

                if (Auth::user()->user_type==1){

                    return redirect('admin/dashboard');
                }
    
                if (Auth::user()->user_type==2){
    
                    return redirect('trainee/dashboard');
                }

                    // return redirect('admin/dashboard');
                
            // dd($request->all());
          }

          else{

            return redirect()->back()->with('error', 'Please Enter correct Credentials');
           }

        // if (!empty(Auth::check())){
        //     return redirect('admin/dashboard');
        // }
    }

    public function forgetpassword(){

        return view('auth.forget');
    }

    public function PostForgetPassword(Request $request){

        $user = User::getEmailSingle($request -> email);
        // dd($user);

        if(!empty($user)){

            $user->remember_token = Str::random(30);

            $user->save();

            Mail::to($user->email)->send(new ForgetPasswordMail($user));

            return redirect()->back()->with('success', 'Please check your email and reset your password');

        }else{
            return redirect()->back()->with('error', 'The User Is not Found');

        }
    }

    public function ResetPassword($remember_token){

        $user = User::getSingleToken($remember_token);

        if(!empty($user)){

            $data ['user'] = $user;

            return view('auth.reset',$data);

        }

        else{

            abort(404);
        }

        // dd($token);
    }

    public function PostReset($token, Request $request){
        if($request->password==$request->cpassword){

            $user= User::getSingleToken($token);
            $user->password = Hash::make($request->password);
            $user->remember_token = Str::random(30);
            $user-> save();
            return redirect(url(''))->with('success',"Password Reset Successfully");
        }
        else{

            return redirect()->back()->with('error',"Password does not match");
        }

    }

    public function logout(){

        Auth::logout();

        return redirect('');
    }

}