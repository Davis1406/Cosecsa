<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecureHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('Strict-Transport-Security', 'max-age=63072000; includeSubDomains; preload');

// Explicit origin listed alongside 'self' so the policy survives
// if a Cloudflare-injected second CSP header replaces 'self' with
// an explicit allowlist (two CSP headers are ANDed by browsers).
$cspOrigin = 'https://cosecsamis.org';
$response->headers->set('Content-Security-Policy',
    "default-src 'self' {$cspOrigin}; " .
    "script-src 'self' {$cspOrigin} 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://speedcf.cloudflareaccess.com; " .
    "style-src 'self' {$cspOrigin} 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://cdnjs.cloudflare.com https://code.ionicframework.com https://speedcf.cloudflareaccess.com; " .
    "font-src 'self' {$cspOrigin} https://fonts.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://code.ionicframework.com; " .
    "img-src 'self' {$cspOrigin} data: https:; " .
    "connect-src 'self' {$cspOrigin} https://cdn.jsdelivr.net; " .
    "object-src 'none'; " .
    "frame-ancestors 'self' {$cspOrigin};"
);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        return $response;
    }
}
