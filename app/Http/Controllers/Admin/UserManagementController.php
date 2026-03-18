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
        return Inertia::render('Admin/Users/Create', [
            'roles' => \App\Models\Role::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(\App\Http\Requests\StoreUserRequest $request)
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['timezone'] = $validated['timezone'] ?? 'Asia/Manila';

        $user = \App\Models\User::create($validated);

        return redirect()->route('admin.users.show', $user->id)
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $user = \App\Models\User::with('role')->findOrFail($id);

        // Get user's tickets with status and priority
        $tickets = \App\Models\Ticket::with(['status', 'priority'])
            ->where('created_by', $id)
            ->orWhere('assigned_to', $id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($ticket) => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'status' => $ticket->status->name ?? 'Unknown',
                'priority' => $ticket->priority->name ?? 'Unknown',
                'created_at' => $ticket->created_at->toDateTimeString(),
            ]);

        // Calculate user stats
        $stats = [
            'total_tickets' => \App\Models\Ticket::where('created_by', $id)->count(),
            'open_tickets' => \App\Models\Ticket::where('created_by', $id)->whereHas('status', fn($q) => $q->whereIn('name', ['Open', 'Pending']))->count(),
            'resolved_tickets' => \App\Models\Ticket::where('created_by', $id)->whereHas('status', fn($q) => $q->where('name', 'Resolved'))->count(),
            'avg_response_time' => $this->calculateAvgResponseTime($id),
        ];

        // Get user's activity
        $activity_logs = \App\Models\TicketActivityLog::with('user:id,username')
            ->where('user_id', $id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'action' => $log->action,
                'user_name' => $log->user->username ?? 'System',
                'old_value' => $log->old_value,
                'new_value' => $log->new_value,
                'created_at' => $log->created_at->toDateTimeString(),
            ]);

        $roles = \App\Models\Role::select('id', 'name')->get();

        // Get user's active sessions (Sessions are usually managed by the database driver inherently using DB facade)
        $sessions = [];
        if (DB::connection()->getSchemaBuilder()->hasTable('sessions')) {
            $sessions = DB::table('sessions')
                ->where('user_id', $id)
                ->where('last_activity', '>', now()->subDays(1)->timestamp)
                ->get()
                ->map(fn ($session) => [
                    'id' => $session->id,
                    'ip' => $session->ip_address,
                    'device' => $this->parseUserAgent($session->user_agent ?? ''),
                    'last_active' => \Carbon\Carbon::createFromTimestamp($session->last_activity)->toDateTimeString(),
                ])->toArray();
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
                'role_name' => $user->role->name ?? 'Unknown',
                'is_active' => (bool) $user->is_active,
                'email_verified' => (bool) $user->email_verified,
                'last_login' => $user->last_login ? $user->last_login->toDateTimeString() : null,
                'created_at' => $user->created_at->toDateTimeString(),
                'updated_at' => $user->updated_at->toDateTimeString(),
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
        $user = \App\Models\User::findOrFail($id);
        $roles = \App\Models\Role::select('id', 'name')->get();

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(\App\Http\Requests\UpdateUserRequest $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->update($request->validated());

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

        \App\Models\User::findOrFail($id)->update([
            'password' => Hash::make($validated['password']),
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

        $user = \App\Models\User::findOrFail($id);
        
        if ($request->hasFile('avatar')) {
            // Delete old avatar if it exists
            if ($user->avatar_url) {
                $oldPath = storage_path('app/public/' . $user->avatar_url);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            // Upload new avatar and update database using Eloquent
            $user->update([
                'avatar_url' => $request->file('avatar')->store('avatars', 'public'),
            ]);
        }

        return redirect()->back()->with('success', 'Avatar updated successfully.');
    }

    /**
     * Impersonate user.
     */
    public function impersonate($id)
    {
        session()->put('impersonate', [
            'admin_id' => Auth::id(),
        ]);

        $targetUser = \App\Models\User::with('role')->findOrFail($id);

        if ($targetUser->role->name === 'admin') {
            abort(403, 'Cannot impersonate another admin.');
        }
        Auth::loginUsingId($id);

        $roleName = $targetUser->role?->name ?? 'user';

        $dashboardRoutes = [
            'admin'   => 'admin.dashboard',
            'manager' => 'manager.dashboard',
            'agent'   => 'agent.dashboard',
            'user'    => 'user.dashboard',
        ];

        return redirect()->route($dashboardRoutes[$roleName] ?? 'user.dashboard');
    }

    /**
     * Stop impersonating and return to admin.
     */
    public function stopImpersonate()
    {
        $impersonate = session()->get('impersonate');

        if ($impersonate && isset($impersonate['admin_id'])) {

            Auth::loginUsingId($impersonate['admin_id']);

            session()->forget('impersonate');
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

        // Eloquent handles soft deleting automatically!
        \App\Models\User::findOrFail($id)->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Bulk remove the specified users.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No users selected.');
        }

        // Prevent deleting yourself
        if (in_array(Auth::id(), $ids)) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        \App\Models\User::whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', count($ids) . ' users deleted successfully.');
    }

    /**
     * Bulk update the specified users.
     */
    public function bulkUpdate(Request $request)
    {
        $ids = $request->input('ids', []);
        $status = $request->input('status');

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No users selected.');
        }

        $query = \App\Models\User::whereIn('id', $ids);

        if ($status !== null) {
            $query->update(['is_active' => $status === 'active']);
        }

        return redirect()->back()->with('success', count($ids) . ' users updated successfully.');
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