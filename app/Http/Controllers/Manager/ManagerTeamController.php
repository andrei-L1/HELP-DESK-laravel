<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Role;

class ManagerTeamController extends Controller
{
    public function index(Request $request)
    {
        $userDepartments = Auth::user()->departments()->pluck('departments.id');

        // Get users who belong to the manager's departments (only agents and users)
        $query = User::with('role')
            ->whereHas('departments', function($q) use ($userDepartments) {
                $q->whereIn('departments.id', $userDepartments);
            })
            ->whereHas('role', function($q) {
                $q->whereIn('name', ['agent', 'user']);
            })
            ->orderByDesc('created_at');

        // Apply filters
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('role')) {
            $query->where('role_id', $request->role);
        }

        $users = $query->paginate(15)->withQueryString();

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

        // Calculate stats specific to the manager's team
        $baseQuery = User::whereHas('departments', function($q) use ($userDepartments) {
            $q->whereIn('departments.id', $userDepartments);
        })->whereHas('role', function($q) {
            $q->whereIn('name', ['agent', 'user']);
        });

        $stats = [
            'total'     => (clone $baseQuery)->count(),
            'active'    => (clone $baseQuery)->where('is_active', true)->count(),
            'inactive'  => (clone $baseQuery)->where('is_active', false)->count(),
            'verified'  => (clone $baseQuery)->where('email_verified', true)->count(),
            'unverified'=> (clone $baseQuery)->where('email_verified', false)->count(),
        ];

        // Roles they can filter by (only agent and user)
        $roles = Role::whereIn('name', ['agent', 'user'])->pluck('name', 'id')->toArray();
        $statuses = ['active' => 'Active', 'inactive' => 'Inactive'];

        return Inertia::render('Manager/Team/Index', [
            'users'    => $users,
            'filters'  => $request->only(['search', 'status', 'role']),
            'roles'    => $roles,
            'statuses' => $statuses,
            'stats'    => $stats,
        ]);
    }

    public function show(int $id)
    {
        $userDepartments = Auth::user()->departments()->pluck('departments.id');

        // Allow viewing only if they are an agent/user and share a department
        $member = clone \App\Models\User::with(['role', 'departments'])
            ->where('id', $id)
            ->whereHas('departments', function($q) use ($userDepartments) {
                $q->whereIn('departments.id', $userDepartments);
            })
            ->whereHas('role', function($q) {
                $q->whereIn('name', ['agent', 'user']);
            })
            ->firstOrFail();

        // Calculate basic historical performance metrics
        $ticketsTotal = \App\Models\Ticket::where('assigned_to', $member->id)->count();
        $ticketsOpen = \App\Models\Ticket::where('assigned_to', $member->id)
            ->whereHas('status', function($q) {
                $q->whereNotIn('name', ['Closed', 'Resolved']);
            })->count();
        $ticketsResolved = \App\Models\Ticket::where('assigned_to', $member->id)
            ->whereHas('status', function($q) {
                $q->whereIn('name', ['Closed', 'Resolved']);
            })->count();

        $recentTickets = \App\Models\Ticket::with(['status', 'priority', 'department'])
            ->where('assigned_to', $member->id)
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        return Inertia::render('Manager/Team/Show', [
            'member' => $member,
            'stats' => [
                'tickets_total'    => $ticketsTotal,
                'tickets_open'     => $ticketsOpen,
                'tickets_resolved' => $ticketsResolved,
            ],
            'recentTickets' => $recentTickets
        ]);
    }
}
