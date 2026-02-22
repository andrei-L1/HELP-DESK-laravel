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

        // Explode permissions separated by '|'
        $permissions = explode('|', $permission);
        $hasPermission = false;

        foreach ($permissions as $perm) {
            if ($user->hasPermission($perm)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}