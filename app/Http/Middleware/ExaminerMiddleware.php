<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;

class ExaminerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Define routes that should bypass examiner authentication (for QR code access)
        $publicRoutes = [
            'examiner/confirm-attendance/*',
            'examiner/confirm-attendance-registration/*'
        ];

        // Check if current route should bypass authentication
        foreach ($publicRoutes as $route) {
            if ($request->is($route)) {
                return $next($request);
            }
        }

        // Regular examiner authentication check
        if (Auth::check()) {
            if (Auth::user()->user_type == 9) {
                return $next($request);
            } else {
                Auth::logout();
                return redirect('')->with('error', 'You are not authorized to access this page.');
            }
        } else {
            Auth::logout();
            return redirect('')->with('error', 'Page expired. Please log in again.');
        }
    }
}