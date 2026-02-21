<?php

namespace App\Http\Middleware;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{   

    public function handle(Request $request, Closure $next, string $permission)
    {
        /** @var User */
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Check if user has the permission (admin check is inside hasPermission now)
        if (!$user->hasPermission($permission)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}