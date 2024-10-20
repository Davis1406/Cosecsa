<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;

class AdminMiddleWare
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
        // Check if the user is authenticated
        if (Auth::check()) {

            // Check if the authenticated user is an admin (user_type == 1)
            if (Auth::user()->user_type == 1) {
                return $next($request);
            } 
            
            // If user_type is not 1, log the user out and redirect
            else {
                Auth::logout();
                return redirect('login')->with('error', 'You are not authorized to access this page.');
            }
        } 
        
        // If not authenticated, log the user out and redirect to login with a session expiry message
        else {
            Auth::logout();
            return redirect('login')->with('error', 'Session expired. Please log in again.');
        }
    }
}
