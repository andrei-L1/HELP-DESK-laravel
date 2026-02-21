<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $roles)
    {
        // Roles come as comma-separated string
        $rolesArray = array_map('strtolower', explode(',', $roles));

        $userRole = strtolower($request->user()?->role?->name ?? '');

        if (! $userRole || ! in_array($userRole, $rolesArray)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}