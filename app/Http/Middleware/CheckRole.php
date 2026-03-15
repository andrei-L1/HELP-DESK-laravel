<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Hold your horses! You need to be logged in to view this page.');
        }

        $roles = explode('|', $role);
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $hasRole = false;

        foreach ($roles as $r) {
            if ($user->hasRole($r)) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            return redirect()->back()->with('error', 'Access Denied: You don\'t have the required role to perform this action! 🚧');
        }

        return $next($request);
    }
}
