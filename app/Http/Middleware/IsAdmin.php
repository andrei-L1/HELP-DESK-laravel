<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            abort(403);
        }

        /** @var User $user */
        $user = Auth::user();

        // Check if user is active
        if (!$user->is_active) {
            abort(403, 'Your account is inactive.');
        }

        // Eager load the role relationship to avoid N+1 queries
        $user->load('role');

        // Check if user has admin role (by name, more reliable than ID)
        $roleName = $user->role?->name ?? null;
        if ($roleName !== 'admin') {
            abort(403);
        }

        return $next($request);
    }
}
