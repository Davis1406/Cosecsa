<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ImpersonationController extends Controller
{
    // role_type => dashboard path, mirrors AuthController::redirectToDashboard()
    protected const DASHBOARDS = [
        1 => 'admin/dashboard',
        2 => 'trainee/dashboard',
        3 => 'candidate/dashboard',
        4 => 'trainer/dashboard',
        5 => 'country-rep/dashboard',
        7 => 'fellow/dashboard',
        8 => 'member/dashboard',
        9 => 'examiner/dashboard',
    ];

    /**
     * Admin-initiated: log in as $id, keeping the admin's own identity
     * stashed in session so they can return to it via stop().
     */
    public function start($id)
    {
        $admin = Auth::user();
        $target = User::find($id);

        if (! $target || $target->is_deleted) {
            return back()->with('error', 'User not found');
        }
        if ($target->id === $admin->id) {
            return back()->with('error', 'You are already logged in as yourself.');
        }
        if (session('impersonator_id')) {
            return back()->with('error', 'Already impersonating a user — return to your admin account first.');
        }

        $activeRole = $target->getRoles()[0] ?? null;
        if (! $activeRole || ! isset(self::DASHBOARDS[$activeRole])) {
            return back()->with('error', 'This user has no active role to view a dashboard as.');
        }

        DB::table('impersonation_logs')->insert([
            'admin_id' => $admin->id,
            'target_user_id' => $target->id,
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session([
            'impersonator_id' => $admin->id,
            'impersonator_role' => $admin->getActiveRole(),
        ]);

        Auth::login($target);
        session(['active_role' => $activeRole]);

        return redirect(self::DASHBOARDS[$activeRole])->with('success', "Now viewing as {$target->name}");
    }

    /**
     * User-initiated (from the impersonation banner): restore the original
     * admin session. Not behind the 'admin' middleware, since the current
     * session is the impersonated user, not an admin — guarded instead by
     * requiring the impersonator_id session key set in start().
     */
    public function stop()
    {
        $impersonatorId = session('impersonator_id');
        if (! $impersonatorId) {
            return redirect('/');
        }

        DB::table('impersonation_logs')
            ->where('admin_id', $impersonatorId)
            ->where('target_user_id', Auth::id())
            ->whereNull('ended_at')
            ->orderByDesc('id')
            ->limit(1)
            ->update(['ended_at' => now(), 'updated_at' => now()]);

        $admin = User::find($impersonatorId);
        $originalRole = session('impersonator_role');

        session()->forget(['impersonator_id', 'impersonator_role']);

        if (! $admin) {
            Auth::logout();
            return redirect('/')->with('error', 'Original admin account no longer exists.');
        }

        Auth::login($admin);
        session(['active_role' => $originalRole]);

        return redirect('admin/dashboard');
    }
}
