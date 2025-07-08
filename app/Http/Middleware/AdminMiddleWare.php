<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Define routes that should bypass admin authentication (for QR code access)
        $publicRoutes = [
            'admin/exams/confirm-attendance/*',
            'admin/exams/confirm-attendance-registration/*'
        ];

        // Check if current route should bypass authentication
        foreach ($publicRoutes as $route) {
            if ($request->is($route)) {
                return $next($request);
            }
        }

        // Regular admin authentication check
          if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasRole(1) && $user->getActiveRole() == 1) {
                return $next($request);
            } else {
                Auth::logout();
                return redirect('')->with('error', 'You are not authorized to access this page.');
            }
        } else {
            Auth::logout();
            return redirect('')->with('error', 'Session expired. Please log in again.');
        }
    }
}