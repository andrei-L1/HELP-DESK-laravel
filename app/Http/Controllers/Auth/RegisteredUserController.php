<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'username' => ['required', 'string', 'max:60', 'unique:'.User::class],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:120', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        // Only require profile fields if they were sent (final step)
        if ($request->has('first_name') || $request->has('last_name')) {
            $rules['first_name']  = ['required', 'string', 'max:120'];
            $rules['last_name']   = ['required', 'string', 'max:120'];
            $rules['middle_name'] = ['nullable', 'string', 'max:120'];
            $rules['display_name']= ['nullable', 'string', 'max:80'];
            $rules['phone']       = ['nullable', 'string', 'max:30'];
        }

        $validated = $request->validate($rules);

        $user = User::create([
            'username'       => $validated['username'],
            'first_name'     => $validated['first_name'] ?? null,
            'last_name'      => $validated['last_name'] ?? null,
            'middle_name'    => $validated['middle_name'] ?? null,
            'display_name'   => $validated['display_name'] ?? null,
            'phone'          => $validated['phone'] ?? null,
            'email'          => $validated['email'],
            'password'       => Hash::make($validated['password']),
            'role_id'        => 1,
            'timezone'       => 'Asia/Manila',
            'is_active'      => true,
            'email_verified' => false,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect("/login");
    }
}