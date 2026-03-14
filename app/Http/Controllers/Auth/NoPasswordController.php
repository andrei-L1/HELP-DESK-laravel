<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class NoPasswordController extends Controller
{
    /**
     * Update password for users who already have one
     */
    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('status', 'Password updated successfully!');
    }

    /**
     * Set password for OAuth users (first time)
     */
    public function set(Request $request)
    {
        $user = $request->user();
        
        // Check if user already has a password
        if (!is_null($user->password)) {
            return back()->withErrors([
                'error' => 'You already have a password. Please use the change password option.'
            ]);
        }

        $request->validate([
            'new_password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('status', 'Password set successfully! You can now login with email and password.');
    }
}