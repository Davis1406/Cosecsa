<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the Candidate / Programme Director / Country Rep / Member dashboards: `portal:5` lets through only
 * someone who holds that role and is currently working as it (the role they picked at login or switched to).
 */
class PortalMiddleware
{
    public function handle(Request $request, Closure $next, int $role): Response
    {
        if (! Auth::check()) {
            return redirect('')->with('error', 'Session expired. Please log in again.');
        }

        $user = Auth::user();
        if ($user->hasRole($role) && $user->getActiveRole() == $role) {
            return $next($request);
        }

        // Signed in, but working as another role: "/" sends them to that role's own dashboard.
        return redirect('')->with('error', 'You are not authorized to access this page.');
    }
}
