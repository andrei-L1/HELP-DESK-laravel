<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleSocialiteController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {

            if ($request->has('error')) {
                return redirect()->route('login')
                    ->with('error', 'Google authentication failed: ' . $request->error);
            }

            $googleUser = Socialite::driver('google')->user();

            // Find user by email
            $existingUser = User::where('email', $googleUser->getEmail())->first();

            if ($existingUser) {

                $updateData = [
                    'last_login' => now(),
                ];

                // Attach Google account if not already linked
                if (!$existingUser->google_id) {
                    $updateData['google_id'] = $googleUser->getId();
                }

                // Always update google avatar (safe because it's separate)
                $updateData['google_avatar'] = $googleUser->getAvatar();

                $existingUser->update($updateData);

                Auth::login($existingUser);

                return $this->redirectUser($existingUser);
            }

            /*
            |--------------------------------------------------------------------------
            | Create New User
            |--------------------------------------------------------------------------
            */

            $nameParts = explode(' ', $googleUser->getName(), 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            // Generate unique username
            $baseUsername = explode('@', $googleUser->getEmail())[0];
            $username = $baseUsername;
            $counter = 1;

            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            $defaultRole = Role::where('name', 'user')->first();

            if (!$defaultRole) {
                Log::error('Default role "user" not found');

                return redirect()->route('login')
                    ->with('error', 'System configuration error. Please contact support.');
            }

            $user = User::create([
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'display_name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),

                'google_id' => $googleUser->getId(),
                'google_avatar' => $googleUser->getAvatar(),

                'password' => Hash::make(Str::random(24)),

                'role_id' => $defaultRole->id,

                'email_verified' => true,
                'email_verified_at' => now(),

                'is_active' => true,
                'last_login' => now(),
            ]);

            Auth::login($user);

            session()->flash('status', 'Welcome to HelpDesk! Your account has been created successfully.');

            return $this->redirectUser($user);

        } catch (\Exception $e) {

            Log::error('Google login error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('login')
                ->with('error', 'Unable to login with Google. Please try again or use email login.');
        }
    }

    private function redirectUser(User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->isManager()) {
            return redirect()->intended(route('manager.dashboard'));
        }

        if ($user->isAgent()) {
            return redirect()->intended(route('agent.dashboard'));
        }

        return redirect()->intended(route('user.dashboard'));
    }
}