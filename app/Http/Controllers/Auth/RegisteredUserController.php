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
        $request->validate([
            'username'          => ['required', 'string', 'max:60', 'unique:'.User::class],
            'first_name'        => ['required', 'string', 'max:120'],
            'last_name'         => ['required', 'string', 'max:120'],
            'middle_name'       => ['nullable', 'string', 'max:120'],
            'display_name'      => ['nullable', 'string', 'max:80'],
            'phone'             => ['nullable', 'string', 'max:30'],
            'email'             => ['required', 'string', 'lowercase', 'email', 'max:120', 'unique:'.User::class],
            'password'          => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'username'      => $request->username,
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'middle_name'   => $request->middle_name ?? null,
            'display_name'  => $request->display_name ?? null,
            'phone'         => $request->phone ?? null,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'role_id'       => 1,                    // default role
            'timezone'      => 'Asia/Manila',
            'is_active'     => true,
            'email_verified' => false,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect('/login');  // or RouteServiceProvider::HOME
    }
}