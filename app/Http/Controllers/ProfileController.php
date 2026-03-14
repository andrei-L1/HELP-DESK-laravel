<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Avatar Upload
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('avatar')) {
            // Delete previous uploaded avatar (if exists)
            if ($user->avatar_url) {
                Storage::disk('public')->delete($user->avatar_url);
            }

            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar_url'] = $path;
        }

        /*
        |--------------------------------------------------------------------------
        | Remove Uploaded Avatar
        |--------------------------------------------------------------------------
        */

        if ($request->boolean('remove_avatar')) {
            if ($user->avatar_url) {
                Storage::disk('public')->delete($user->avatar_url);
            }
            $data['avatar_url'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Handle Google Avatar Visibility
        |--------------------------------------------------------------------------
        */
        
        // IMPORTANT: Explicitly handle hide_google_avatar checkbox
        if ($request->has('hide_google_avatar')) {
            // Convert to boolean (checkbox returns "1" or "0" as string)
            $data['hide_google_avatar'] = filter_var(
                $request->input('hide_google_avatar'), 
                FILTER_VALIDATE_BOOLEAN
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Update Profile
        |--------------------------------------------------------------------------
        */

        $user->fill($data);

        // Reset email verification if email changed
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
            $user->email_verified = false;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        // Delete uploaded avatar if exists
        if ($user->avatar_url) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}