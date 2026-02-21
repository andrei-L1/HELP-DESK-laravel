<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    /**
     * Show the user creation form.
     */
    public function create()
    {
        $roles = DB::table('roles')->get(['id', 'name']);
        
        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:60|unique:users',
            'email' => 'required|email|max:120|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'required|string|max:120',
            'last_name' => 'required|string|max:120',
            'middle_name' => 'nullable|string|max:120',
            'display_name' => 'nullable|string|max:80',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:30',
            'timezone' => 'nullable|string|max:40',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['timezone'] = $validated['timezone'] ?? 'Asia/Manila';

        $userId = DB::table('users')->insertGetId($validated);

        return redirect()->route('admin.users.show', $userId)
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $user = DB::table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.id', $id)
            ->whereNull('users.deleted_at')
            ->select(
                'users.id',
                'users.username',
                'users.email',
                'users.first_name',
                'users.last_name',
                'users.middle_name',
                'users.display_name',
                'users.avatar_url',
                'users.phone',
                'users.timezone',
                'users.is_active',
                'users.email_verified',
                'users.last_login',
                'users.created_at',
                'users.updated_at',
                'roles.id as role_id',
                'roles.name as role_name'
            )
            ->first();

        if (!$user) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found.');
        }

        // Get user's tickets with status and priority names
        $tickets = DB::table('tickets')
            ->leftJoin('ticket_statuses', 'tickets.status_id', '=', 'ticket_statuses.id')
            ->leftJoin('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
            ->where('tickets.created_by', $id)
            ->orWhere('tickets.assigned_to', $id)
            ->select(
                'tickets.id',
                'tickets.ticket_number',
                'tickets.subject',
                'ticket_statuses.name as status',
                'ticket_priorities.name as priority',
                'tickets.created_at'
            )
            ->orderByDesc('tickets.created_at')
            ->limit(10)
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'subject' => $ticket->subject,
                    'status' => $ticket->status ?? 'Unknown',
                    'priority' => $ticket->priority ?? 'Unknown',
                    'created_at' => \Carbon\Carbon::parse($ticket->created_at)->toDateTimeString(),
                ];
            });

        // Calculate user stats using status IDs
        $openStatusIds = DB::table('ticket_statuses')
            ->whereIn('name', ['Open', 'Pending'])
            ->pluck('id')
            ->toArray();

        $resolvedStatusIds = DB::table('ticket_statuses')
            ->where('name', 'Resolved')
            ->pluck('id')
            ->toArray();

        $stats = [
            'total_tickets' => DB::table('tickets')
                ->where('created_by', $id)
                ->count(),
            'open_tickets' => DB::table('tickets')
                ->where('created_by', $id)
                ->whereIn('status_id', $openStatusIds)
                ->count(),
            'resolved_tickets' => DB::table('tickets')
                ->where('created_by', $id)
                ->whereIn('status_id', $resolvedStatusIds)
                ->count(),
            'avg_response_time' => $this->calculateAvgResponseTime($id),
        ];

        // Get user's activity from ticket_activity_logs
        $activity_logs = DB::table('ticket_activity_logs')
            ->where('user_id', $id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'user_name' => DB::table('users')->where('id', $log->user_id)->value('username') ?? 'System',
                    'old_value' => $log->old_value,
                    'new_value' => $log->new_value,
                    'created_at' => \Carbon\Carbon::parse($log->created_at)->toDateTimeString(),
                ];
            });

        // Get all roles for the edit form
        $roles = DB::table('roles')->get(['id', 'name']);

        // Get user's active sessions
        $sessions = [];
        if (DB::connection()->getSchemaBuilder()->hasTable('sessions')) {
            $sessions = DB::table('sessions')
                ->where('user_id', $id)
                ->where('last_activity', '>', now()->subDays(1)->timestamp)
                ->get()
                ->map(function ($session) {
                    return [
                        'id' => $session->id,
                        'ip' => $session->ip_address,
                        'device' => $this->parseUserAgent($session->user_agent ?? ''),
                        'last_active' => \Carbon\Carbon::createFromTimestamp($session->last_activity)->toDateTimeString(),
                    ];
                })->toArray();
        }

        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'middle_name' => $user->middle_name,
                'display_name' => $user->display_name,
                'full_name' => trim("{$user->first_name} {$user->middle_name} {$user->last_name}"),
                'avatar_url' => $user->avatar_url,
                'phone' => $user->phone,
                'timezone' => $user->timezone,
                'role_id' => $user->role_id,
                'role_name' => $user->role_name,
                'is_active' => (bool) $user->is_active,
                'email_verified' => (bool) $user->email_verified,
                'last_login' => $user->last_login ? \Carbon\Carbon::parse($user->last_login)->toDateTimeString() : null,
                'created_at' => \Carbon\Carbon::parse($user->created_at)->toDateTimeString(),
                'updated_at' => \Carbon\Carbon::parse($user->updated_at)->toDateTimeString(),
                'sessions' => $sessions,
            ],
            'roles' => $roles,
            'activity_logs' => $activity_logs,
            'tickets' => $tickets,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit($id)
    {
        $user = DB::table('users')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found.');
        }

        $roles = DB::table('roles')->get(['id', 'name']);

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        $user = DB::table('users')->where('id', $id)->first();
        
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $validated = $request->validate([
            'username' => "required|string|max:60|unique:users,username,{$id}",
            'email' => "required|email|max:120|unique:users,email,{$id}",
            'first_name' => 'required|string|max:120',
            'last_name' => 'required|string|max:120',
            'middle_name' => 'nullable|string|max:120',
            'display_name' => 'nullable|string|max:80',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:30',
            'timezone' => 'nullable|string|max:40',
            'is_active' => 'boolean',
        ]);

        DB::table('users')
            ->where('id', $id)
            ->update($validated);

        return redirect()->route('admin.users.show', $id)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request, $id)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::table('users')
            ->where('id', $id)
            ->update([
                'password' => Hash::make($validated['password']),
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    /**
     * Update user avatar.
     */
    public function updateAvatar(Request $request, $id)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            
            DB::table('users')
                ->where('id', $id)
                ->update([
                    'avatar_url' => $path,
                    'updated_at' => now(),
                ]);
        }

        return redirect()->back()->with('success', 'Avatar updated successfully.');
    }

    /**
     * Impersonate user.
     */
    public function impersonate($id)
    {
        // Store original admin ID in session
        session()->put('impersonate', Auth::id());
        
        // Log in as the target user
        Auth::loginUsingId($id);
        
        return redirect()->route('dashboard');
    }

    /**
     * Stop impersonating and return to admin.
     */
    public function stopImpersonate()
    {
        $originalId = session()->get('impersonate');
        
        if ($originalId) {
            session()->forget('impersonate');
            Auth::loginUsingId($originalId);
        }
        
        return redirect()->route('admin.users.index');
    }

    /**
     * Logout a specific session.
     */
    public function logoutSession(Request $request, $userId, $sessionId)
    {
        if (DB::connection()->getSchemaBuilder()->hasTable('sessions')) {
            DB::table('sessions')
                ->where('id', $sessionId)
                ->where('user_id', $userId)
                ->delete();
        }

        return redirect()->back()->with('success', 'Session logged out successfully.');
    }

    /**
     * Logout all sessions for a user.
     */
    public function logoutAllSessions(Request $request, $id)
    {
        if (DB::connection()->getSchemaBuilder()->hasTable('sessions')) {
            DB::table('sessions')
                ->where('user_id', $id)
                ->delete();
        }

        return redirect()->back()->with('success', 'All sessions logged out successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy($id)
    {
        // Don't allow deleting yourself
        if (Auth::id() == $id) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        // Soft delete
        DB::table('users')
            ->where('id', $id)
            ->update([
                'deleted_at' => now(),
            ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Calculate average response time for user's tickets.
     */
    private function calculateAvgResponseTime($userId)
    {
        // Check if tickets table has first_response_at column
        $columns = DB::connection()->getSchemaBuilder()->getColumnListing('tickets');
        
        if (!in_array('first_response_at', $columns)) {
            return null;
        }

        $avgTime = DB::table('tickets')
            ->where('assigned_to', $userId)
            ->whereNotNull('first_response_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, first_response_at)) as avg_minutes'))
            ->first();

        if (!$avgTime || !$avgTime->avg_minutes) {
            return null;
        }

        $minutes = round($avgTime->avg_minutes);
        
        if ($minutes < 60) {
            return $minutes . ' min';
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        return $remainingMinutes ? "{$hours}h {$remainingMinutes}m" : "{$hours}h";
    }

    /**
     * Parse user agent string to get device info.
     */
    private function parseUserAgent($userAgent)
    {
        if (!$userAgent) {
            return 'Unknown Device';
        }

        $userAgent = strtolower($userAgent);

        if (strpos($userAgent, 'windows') !== false) {
            return 'Windows';
        } elseif (strpos($userAgent, 'mac') !== false && strpos($userAgent, 'iphone') === false) {
            return 'macOS';
        } elseif (strpos($userAgent, 'linux') !== false) {
            return 'Linux';
        } elseif (strpos($userAgent, 'iphone') !== false) {
            return 'iPhone';
        } elseif (strpos($userAgent, 'ipad') !== false) {
            return 'iPad';
        } elseif (strpos($userAgent, 'android') !== false) {
            return 'Android';
        }

        return 'Unknown Device';
    }
}