<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Models\User;

class LoginController extends Controller
{
    public function show()
    {
        return inertia('Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'The provided credentials do not match our records.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // Get role name safely (fallback to 'user' if no role or relation missing)
        $roleName = $user->role?->name ?? 'user';

        // Map role names → named routes
        $redirects = [
            'admin'   => 'admin.admindashboard',
            'manager' => 'manager.dashboard',
            'user'    => 'dashboard',
            // Add more roles here in the future if needed, e.g.:
            // 'editor'  => 'editor.content',
        ];

        $routeName = $redirects[$roleName] ?? 'dashboard';

        // Safety check: route must exist
        if (! Route::has($routeName)) {
            Log::warning(
                "Login redirect failed: route '{$routeName}' does not exist " .
                "for user ID {$user->id} (role: {$roleName})"
            );
            $routeName = 'dashboard';
        }

        // Optional: friendly feedback
        if ($roleName === 'admin') {
            session()->flash('status', 'Welcome, Administrator!');
        } elseif ($roleName === 'manager') {
            session()->flash('status', 'Welcome back, Manager!');
        }

        return redirect()->route($routeName);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}