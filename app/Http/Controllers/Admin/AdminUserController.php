<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        return $this->getUsers($request, 'Admin/Users/Index'); 
    }

    public function all(Request $request)
    {
        return $this->getUsers($request, 'Admin/Users/All'); 
    }

    private function getUsers(Request $request, string $component, array $extraFilters = [])
    {
        $query = \App\Models\User::with('role')->orderByDesc('created_at');

        // ── Apply extra fixed filters ──
        foreach ($extraFilters as $column => $value) {
            $query->where($column, $value);
        }

        // ── Free text search ──
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('display_name', 'like', "%{$search}%")
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
            });
        }

        // ── Status / Active filter ──
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // ── Role filter ──
        if ($request->filled('role')) {
            $query->where('role_id', $request->role);
        }

        $users = $query->paginate(15)->withQueryString();

        // Transform for frontend
        $users->getCollection()->transform(function ($user) {
            return [
                'id'            => $user->id,
                'username'      => $user->username,
                'email'         => $user->email,
                'full_name'     => trim("{$user->first_name} {$user->last_name}"),
                'display_name'  => $user->display_name ?? trim("{$user->first_name} {$user->last_name}") ?: '—',
                'avatar_url'    => $user->avatar_url,
                'role'          => $user->role->name ?? 'Unknown',
                'is_active'     => (bool) $user->is_active,
                'email_verified'=> (bool) $user->email_verified,
                'last_login'    => $user->last_login ? $user->last_login->toDateTimeString() : null,
                'created_at'    => $user->created_at->toDateTimeString(),
            ];
        });

        // Calculate stats for dashboard using Eloquent
        $stats = [
            'total'     => \App\Models\User::count(),
            'active'    => \App\Models\User::where('is_active', true)->count(),
            'inactive'  => \App\Models\User::where('is_active', false)->count(),
            'verified'  => \App\Models\User::where('email_verified', true)->count(),
            'unverified'=> \App\Models\User::where('email_verified', false)->count(),
            'with_avatar' => \App\Models\User::whereNotNull('avatar_url')->count(),
        ];

        // Possible filters for dropdowns
        $roles = \App\Models\Role::pluck('name', 'id')->toArray();
        $statuses = ['active' => 'Active', 'inactive' => 'Inactive'];

        return Inertia::render($component, [
            'users'    => $users,
            'filters'  => $request->only(['search', 'status', 'role']),
            'roles'    => $roles,
            'statuses' => $statuses,
            'stats'    => $stats,  // Add stats here
            'view'     => 'all',
        ]);
    }
}