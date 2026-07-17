<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;

class PermissionMiddleware
{
    /**
     * Runs after AdminMiddleWare, so we already know the user is an active
     * admin-type account. This just narrows *which* admin pages they can
     * reach, based on their assigned Role (app/Models/Role.php) and the
     * module map in config/admin_permissions.php.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $path = $request->path();

        $module = $this->resolveModule($path);
        if (! $module) {
            // Unmapped route — fail-open rather than risk locking everyone
            // out of something nobody thought to add to the module map.
            return $next($request);
        }

        // Most GET routes are read-only pages, but several destructive
        // actions in this app are wired as plain GET links (e.g.
        // admin/associates/fellows/delete/{id} deletes immediately, no POST
        // confirmation step) — those must require "manage" too, or a
        // view-only role could delete records just by following a link.
        $segments = explode('/', trim($path, '/'));
        $isDestructiveGet = (bool) array_intersect(['delete', 'destroy', 'impersonate'], $segments);

        $suffix = (in_array($request->method(), ['GET', 'HEAD']) && ! $isDestructiveGet) ? 'view' : 'manage';
        $key = "{$module}.{$suffix}";

        if ($user->hasPermission($key)) {
            return $next($request);
        }

        return redirect('admin/dashboard')->with('error', 'You do not have permission to access that page.');
    }

    protected function resolveModule(string $path): ?string
    {
        $map = config('admin_permissions.route_map', []);

        $best = null;
        $bestLen = -1;
        foreach ($map as $prefix => $module) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                if (strlen($prefix) > $bestLen) {
                    $best = $module;
                    $bestLen = strlen($prefix);
                }
            }
        }

        return $best;
    }
}
