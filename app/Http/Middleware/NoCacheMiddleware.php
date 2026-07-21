<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoCacheMiddleware
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
        $response = $next($request);

        // Inline PDFs (e.g. transcripts) must not carry no-store — several
        // browsers' built-in PDF viewers simply refuse to render a resource
        // marked no-store, showing a blank tab even though the bytes are a
        // valid PDF. Every other response keeps the strict no-cache policy.
        if (str_starts_with((string) $response->headers->get('Content-Type'), 'application/pdf')) {
            return $response;
        }

        // Set headers to prevent caching
        return $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                        ->header('Pragma', 'no-cache')
                        ->header('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
    }
}
