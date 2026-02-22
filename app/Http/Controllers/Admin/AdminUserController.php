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
        $query = DB::table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->whereNull('users.deleted_at')
            ->select(
                'users.id',
                'users.username',
                'users.email',
                'users.first_name',
                'users.last_name',
                'users.display_name',
                'users.avatar_url',
                'users.is_active',
                'users.email_verified',
                'users.created_at',
                'users.last_login',
                'roles.name as role_name' 
            )
            ->orderByDesc('users.created_at');

        // ── Apply extra fixed filters ──
        foreach ($extraFilters as $column => $value) {
            $query->where("users.{$column}", $value);
        }

        // ── Free text search ──
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('users.username', 'like', "%{$search}%")
                ->orWhere('users.email', 'like', "%{$search}%")
                ->orWhere('users.first_name', 'like', "%{$search}%")
                ->orWhere('users.last_name', 'like', "%{$search}%")
                ->orWhere('users.display_name', 'like', "%{$search}%")
                ->orWhereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$search}%"]);
            });
        }

        // ── Status / Active filter ──
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('users.is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('users.is_active', false);
            }
        }

        // ── Role filter ──
        if ($request->filled('role')) {
            $query->where('users.role_id', $request->role);
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
                'avatar_url'    => $user->avatar_url ? asset('storage/' . $user->avatar_url) : null,
                'role'          => $user->role_name ?? 'Unknown',
                'is_active'     => (bool) $user->is_active,
                'email_verified'=> (bool) $user->email_verified,
                'last_login'    => $user->last_login ? \Carbon\Carbon::parse($user->last_login)->toDateTimeString() : null,
                'created_at'    => \Carbon\Carbon::parse($user->created_at)->toDateTimeString(),
            ];
        });

        // Calculate stats for dashboard
        $stats = [
            'total'     => DB::table('users')->whereNull('deleted_at')->count(),
            'active'    => DB::table('users')->whereNull('deleted_at')->where('is_active', true)->count(),
            'inactive'  => DB::table('users')->whereNull('deleted_at')->where('is_active', false)->count(),
            'verified'  => DB::table('users')->whereNull('deleted_at')->where('email_verified', true)->count(),
            'unverified'=> DB::table('users')->whereNull('deleted_at')->where('email_verified', false)->count(),
            'with_avatar' => DB::table('users')->whereNull('deleted_at')->whereNotNull('avatar_url')->count(),
        ];

        // Possible filters for dropdowns
        $roles = DB::table('roles')->pluck('name', 'id')->toArray();
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