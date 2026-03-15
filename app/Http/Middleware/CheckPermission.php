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
            return redirect()->route('login')->with('error', 'Hold your horses! You need to be logged in to do that.');
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
            return redirect()->back()->with('error', 'Access Denied: Your account lacks the required permissions to perform this specific action! 🛑');
        }

        return $next($request);
    }
}