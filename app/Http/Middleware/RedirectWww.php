<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectWww
{
    public function handle(Request $request, Closure $next)
    {
        if (str_starts_with($request->getHost(), 'www.')) {
            $url = $request->getScheme() . '://'
                . substr($request->getHost(), 4)
                . $request->getRequestUri();

            return redirect()->away($url, 301);
        }

        return $next($request);
    }
}
