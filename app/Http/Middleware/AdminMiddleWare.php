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
        if (Auth::check()) {
            // Check if the authenticated user is an admin (user_type == 1)
            if (Auth::user()->user_type == 1) {
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
