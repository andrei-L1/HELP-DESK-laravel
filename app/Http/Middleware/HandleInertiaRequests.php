<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\User;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $impersonation = null;

        if (session()->has('impersonate')) {
            $session = session('impersonate');

            if (isset($session['admin_id'])) {
                $admin = User::find($session['admin_id']);

                if ($admin) {
                    $impersonation = [
                        'admin' => [
                            'id' => $admin->id,
                            'name' => $admin->display_name ?? trim($admin->first_name . ' ' . $admin->last_name),
                            'email' => $admin->email,
                        ],
                    ];
                }
            }
        }

        return [
            ...parent::share($request),

            'auth' => [
                'user' => $request->user()
                    ? array_merge($request->user()->toArray(), [
                        'role' => $request->user()->role?->name,
                        'permissions' => $request->user()->role
                            ? $request->user()->role->permissions->pluck('name')->toArray()
                            : [],
                    ])
                    : null,
            ],

            // Flash messages for status and error
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
                'error' => fn () => $request->session()->get('error'),
                'success' => fn () => $request->session()->get('success'),
            ],

            // impersonation data
            'impersonation' => $impersonation,

            // simple boolean if needed
            'impersonating' => session()->has('impersonate'),
        ];
    }
}