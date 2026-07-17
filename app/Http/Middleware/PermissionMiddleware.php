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

        // Deleting is Super Admin only, full stop — regardless of what a
        // scoped role's "manage" permission covers. Several delete actions
        // in this app are wired as plain GET links (no POST confirmation
        // step) rather than the DELETE verb, so check both.
        $segments = explode('/', trim($path, '/'));
        $isDelete = $request->isMethod('delete') || (bool) array_intersect(['delete', 'destroy'], $segments);

        if ($isDelete) {
            if ($user->isSuperAdmin()) {
                return $next($request);
            }
            return redirect('admin/dashboard')->with('error', 'Only Super Admin can delete records.');
        }

        // Impersonating is also treated as a "manage" action even though the
        // start route is GET, so a view-only role can't reach it by URL.
        $isImpersonate = in_array('impersonate', $segments);

        $suffix = (in_array($request->method(), ['GET', 'HEAD']) && ! $isImpersonate) ? 'view' : 'manage';
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
