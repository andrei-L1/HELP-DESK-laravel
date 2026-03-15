<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view — pass departments for the department step.
     */
    public function create()
    {
        $departments = DB::table('departments')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'short_code', 'description'])
            ->map(fn ($d) => [
                'id'          => $d->id,
                'name'        => $d->name,
                'short_code'  => $d->short_code,
                'description' => $d->description ?? null,
            ]);

        return Inertia::render('Auth/Register', [
            'departments' => $departments,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'username' => ['required', 'string', 'max:60', 'unique:' . User::class],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:120', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        // Only require profile fields if they were sent (final step)
        if ($request->has('first_name') || $request->has('last_name')) {
            $rules['first_name']   = ['required', 'string', 'max:120'];
            $rules['last_name']    = ['required', 'string', 'max:120'];
            $rules['middle_name']  = ['nullable', 'string', 'max:120'];
            $rules['display_name'] = ['nullable', 'string', 'max:80'];
            $rules['phone']        = ['nullable', 'string', 'max:30'];
            $rules['department_id'] = ['nullable', 'integer', 'exists:departments,id'];
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

        // Assign to selected department (as primary)
        if (!empty($validated['department_id'])) {
            DB::table('user_departments')->insert([
                'user_id'       => $user->id,
                'department_id' => (int) $validated['department_id'],
                'is_primary'    => true,
                'joined_at'     => now(),
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}