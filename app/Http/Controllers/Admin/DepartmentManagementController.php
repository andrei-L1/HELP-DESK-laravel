<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DepartmentManagementController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index(Request $request)
    {
        $query = DB::table('departments')
            ->leftJoin('users as managers', 'departments.manager_id', '=', 'managers.id')
            ->select([
                'departments.id',
                'departments.name',
                'departments.short_code',
                'departments.description',
                'departments.manager_id',
                'departments.is_active',
                'departments.created_at',
                'departments.updated_at',
                DB::raw('CONCAT(managers.first_name, " ", managers.last_name) as manager_name'),
                // Count users through pivot table
                DB::raw('(SELECT COUNT(*) FROM user_departments WHERE user_departments.department_id = departments.id) as users_count'),
                // Count tickets (assuming tickets have department_id)
                DB::raw('(SELECT COUNT(*) FROM tickets WHERE tickets.department_id = departments.id) as tickets_count')
            ]);

        // Apply search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('departments.name', 'like', "%{$search}%")
                ->orWhere('departments.short_code', 'like', "%{$search}%")
                ->orWhere('departments.description', 'like', "%{$search}%")
                ->orWhere('managers.first_name', 'like', "%{$search}%")
                ->orWhere('managers.last_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('departments.is_active', $request->status === 'active');
        }

        // Order by
        $query->orderBy('departments.name');

        // Paginate
        $departments = $query->paginate(15)
            ->through(function ($dept) {
                return [
                    'id' => $dept->id,
                    'name' => $dept->name,
                    'short_code' => $dept->short_code,
                    'description' => $dept->description,
                    'manager_id' => $dept->manager_id,
                    'manager_name' => $dept->manager_name,
                    'is_active' => (bool) $dept->is_active,
                    'users_count' => $dept->users_count,
                    'tickets_count' => $dept->tickets_count,
                    'created_at' => $dept->created_at,
                    'updated_at' => $dept->updated_at,
                ];
            });

        // Get all users for manager dropdown
        $users = DB::table('users')
            ->whereNull('deleted_at')
            ->orderBy('first_name')
            ->select('id', 'first_name', 'last_name', 'email')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                ];
            });

        // Get stats using the pivot table
        $stats = [
            'total_departments' => DB::table('departments')->count(),
            'active_departments' => DB::table('departments')->where('is_active', true)->count(),
            'total_users' => DB::table('user_departments')->distinct('user_id')->count('user_id'),
            'total_assignments' => DB::table('user_departments')->count(),
            'departments_with_managers' => DB::table('departments')->whereNotNull('manager_id')->count(),
        ];

        return Inertia::render('Admin/Departments/Departments', [
            'departments' => $departments,
            'users' => $users,
            'filters' => $request->only(['search', 'status']),
            'stats' => $stats,
        ]);
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:80|unique:departments',
            'short_code' => 'nullable|string|max:20|unique:departments',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        DB::table('departments')->insert([
            'name' => $validated['name'],
            'short_code' => $validated['short_code'] ?? null,
            'description' => $validated['description'] ?? null,
            'manager_id' => $validated['manager_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified department with its users.
     */
    public function show($id)
    {
        $department = DB::table('departments')
            ->leftJoin('users as managers', 'departments.manager_id', '=', 'managers.id')
            ->where('departments.id', $id)
            ->select([
                'departments.*',
                DB::raw('CONCAT(managers.first_name, " ", managers.last_name) as manager_name')
            ])
            ->first();

        if (!$department) {
            return redirect()->back()->with('error', 'Department not found.');
        }

        // Get users assigned to this department
        $assignedUsers = DB::table('user_departments')
            ->join('users', 'user_departments.user_id', '=', 'users.id')
            ->where('user_departments.department_id', $id)
            ->whereNull('users.deleted_at')
            ->select([
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.username',
                'user_departments.is_primary',
                'user_departments.joined_at'
            ])
            ->orderBy('user_departments.is_primary', 'desc')
            ->orderBy('users.first_name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'is_primary' => (bool) $user->is_primary,
                    'joined_at' => $user->joined_at,
                ];
            });

        // Get available users (not assigned to this department)
        $assignedUserIds = $assignedUsers->pluck('id')->toArray();
        
        $availableUsers = DB::table('users')
            ->whereNull('deleted_at')
            ->whereNotIn('id', $assignedUserIds)
            ->orderBy('first_name')
            ->select('id', 'first_name', 'last_name', 'email')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                ];
            });

        return Inertia::render('Admin/Departments/Show', [
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
                'short_code' => $department->short_code,
                'description' => $department->description,
                'manager_id' => $department->manager_id,
                'manager_name' => $department->manager_name,
                'is_active' => (bool) $department->is_active,
                'created_at' => $department->created_at,
                'updated_at' => $department->updated_at,
            ],
            'assignedUsers' => $assignedUsers,
            'availableUsers' => $availableUsers,
        ]);
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, $id)
    {
        $department = DB::table('departments')->where('id', $id)->first();
        
        if (!$department) {
            return redirect()->back()->with('error', 'Department not found.');
        }

        $validated = $request->validate([
            'name' => "required|string|max:80|unique:departments,name,{$id}",
            'short_code' => "nullable|string|max:20|unique:departments,short_code,{$id}",
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        DB::table('departments')
            ->where('id', $id)
            ->update([
                'name' => $validated['name'],
                'short_code' => $validated['short_code'],
                'description' => $validated['description'],
                'manager_id' => $validated['manager_id'],
                'is_active' => $validated['is_active'],
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Assign users to department.
     */
    public function assignUsers(Request $request, $id)
    {
        $department = DB::table('departments')->where('id', $id)->first();

        if (!$department) {
            return redirect()->back()->with('error', 'Department not found.');
        }

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'is_primary' => 'boolean',
        ]);

        $now = now();
        $assignments = [];

        foreach ($validated['user_ids'] as $userId) {
            // Check if already assigned
            $exists = DB::table('user_departments')
                ->where('user_id', $userId)
                ->where('department_id', $id)
                ->exists();

            if (!$exists) {
                $assignments[] = [
                    'user_id' => $userId,
                    'department_id' => $id,
                    'is_primary' => $validated['is_primary'] ?? false,
                    'joined_at' => $now,
                ];
            }
        }

        if (!empty($assignments)) {
            DB::table('user_departments')->insert($assignments);
        }

        return redirect()->back()->with('success', count($assignments) . ' user(s) assigned successfully.');
    }

    /**
     * Remove user from department.
     */
    public function removeUser(Request $request, $id, $userId)
    {
        DB::table('user_departments')
            ->where('department_id', $id)
            ->where('user_id', $userId)
            ->delete();

        return redirect()->back()->with('success', 'User removed from department.');
    }

    /**
     * Set primary department for user.
     */
    public function setPrimary(Request $request, $id, $userId)
    {
        // Remove primary from all user's departments
        DB::table('user_departments')
            ->where('user_id', $userId)
            ->update(['is_primary' => false]);

        // Set primary for this department
        DB::table('user_departments')
            ->where('department_id', $id)
            ->where('user_id', $userId)
            ->update(['is_primary' => true]);

        return redirect()->back()->with('success', 'Primary department updated.');
    }

    /**
     * Remove the specified department.
     */
    public function destroy($id)
    {
        $department = DB::table('departments')->where('id', $id)->first();

        if (!$department) {
            return redirect()->back()->with('error', 'Department not found.');
        }

        // Check if department has users through pivot table
        $userCount = DB::table('user_departments')->where('department_id', $id)->count();
        
        if ($userCount > 0) {
            return redirect()->back()->with('error', 'Cannot delete department that has users assigned.');
        }

        // Check if department has tickets
        $ticketCount = DB::table('tickets')->where('department_id', $id)->count();
        
        if ($ticketCount > 0) {
            return redirect()->back()->with('error', 'Cannot delete department that has tickets assigned.');
        }

        DB::table('departments')->where('id', $id)->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}