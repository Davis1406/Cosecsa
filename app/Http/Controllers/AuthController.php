<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\ForgetPasswordMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if (!session()->has('pending_role')) {
            if ($request->route()->getName() !== 'select.role') {
                return redirect()->route('select.role');
            }
        }

        if (Auth::check()) {
            return $this->redirectToDashboard();
        }

        return view('auth.login');
    }

    public function AuthLogin(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
            'role' => 'required|numeric'
        ]);

        $role = (int) $request->role;
        $remember = $request->has('remember');
        $inputValue = $request->email;

        if (preg_match('/^\d{1,4}$/', $inputValue)) {
            // Fellow ID login — 1–4 digit number, look up in fellows table
            $fellowIdNumber = str_pad($inputValue, 4, '0', STR_PAD_LEFT);
            $fellow = DB::table('fellows')
                ->where('fellow_id_number', $fellowIdNumber)
                ->whereNotNull('user_id')
                ->first();

            if (!$fellow) {
                return redirect()->back()->with('error', 'Invalid credentials');
            }

            $user = User::find($fellow->user_id);
            if (!$user || !Hash::check($request->password, $user->password)) {
                return redirect()->back()->with('error', 'Invalid credentials');
            }

            Auth::login($user, $remember);
        } elseif ($role == 9) {
            // Examiner login — by email or name
            $user = User::where(function ($query) use ($inputValue) {
                    $query->where('email', $inputValue)
                          ->orWhere('name', $inputValue);
                })
                ->whereHas('roles', function ($query) {
                    $query->where('role_type', 9);
                })
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return redirect()->back()->with('error', 'Invalid credentials');
            }

            Auth::login($user, $remember);
        } else {
            // Other roles — email or name depending on input format
            $loginField = filter_var($inputValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
            $credentials = [$loginField => $inputValue, 'password' => $request->password];

            if (!Auth::attempt($credentials, $remember)) {
                return redirect()->back()->with('error', 'Invalid credentials');
            }
        }

        $user = Auth::user();

        if (!$user->hasRole($role)) {
            Auth::logout();
            return redirect()->back()->with('error', 'You are not assigned to the selected role.');
        }

        session(['active_role' => $role]);
        session()->forget('pending_role');

        return $this->redirectToDashboard();
    }

    public function showRoleSelection()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }

        $roleNames = [
            9 => 'Examiner',
            1 => 'Admin',
            4 => 'Programme Director',
            5 => 'Country Representative',
            7 => 'Fellow',
            8 => 'Member'
        ];

        $availableRoles = collect($roleNames)->map(function ($name, $id) {
            return [
                'id' => $id,
                'name' => $name
            ];
        })->values()->toArray();

        return view('auth.role-selection', compact('availableRoles'));
    }

    public function selectRole(Request $request)
    {
        $role = (int) $request->role;

        if (!in_array($role, [1, 2, 3, 4, 5, 7, 8, 9])) {
            return redirect()->route('select.role')->with('error', 'Invalid role selection');
        }

        session(['pending_role' => $role]);

        return redirect()->route('login');
    }

    public function switchRole(Request $request)
    {
        $role = (int) $request->role;

        if (!Auth::user()->hasRole($role)) {
            return redirect()->back()->with('error', 'You are not assigned to this role.');
        }

        session(['active_role' => $role]);

        return $this->redirectToDashboard();
    }

    public function logout()
    {
        Auth::logout();
        session()->flush();
        return redirect()->route('login');
    }

    private function redirectToDashboard()
    {
        $activeRole = Auth::user()->getActiveRole();

        switch ($activeRole) {
            case 1: return redirect('admin/dashboard');
            case 2: return redirect('trainee/dashboard');
            case 3: return redirect('candidate/dashboard');
            case 4: return redirect('programme-director/dashboard');
            case 5: return redirect('country-rep/dashboard');
            case 7: return redirect('fellow/dashboard');
            case 8: return redirect('member/dashboard');
            case 9: return redirect('examiner/dashboard');
            default:
                Auth::logout();
                return redirect('login')->with('error', 'Invalid user role');
        }
    }
}
