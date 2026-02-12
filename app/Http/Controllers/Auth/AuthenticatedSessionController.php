<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        
        // Eager load the role relationship to avoid N+1 queries
        $user->load('role');

        // Get role name safely (fallback to 'user' if no role or relation missing)
        $roleName = $user->role?->name ?? 'user';

        // Map role names → named routes
        $redirects = [
            'admin'   => 'admin.admindashboard',
            'manager' => 'dashboard', // Manager dashboard route doesn't exist yet, using regular dashboard
            'user'    => 'dashboard',
            // Add more roles here in the future if needed
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

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
