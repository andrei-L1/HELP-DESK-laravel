<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RoleManagementController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        $query = DB::table('roles')
            ->select([
                'roles.id',
                'roles.name',
                'roles.description',
                'roles.created_at',
                'roles.updated_at',
                DB::raw('(SELECT COUNT(*) FROM users WHERE users.role_id = roles.id AND users.deleted_at IS NULL) as users_count')
            ]);

        // Apply search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('roles.name', 'like', "%{$search}%")
                  ->orWhere('roles.description', 'like', "%{$search}%");
            });
        }

        // Order by
        $query->orderBy('roles.name');

        // Paginate
        $roles = $query->paginate(15)
            ->through(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'description' => $role->description,
                    'users_count' => $role->users_count,
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at,
                ];
            });

        // Get stats
        $stats = [
            'total_roles' => DB::table('roles')->count(),
            'total_users' => DB::table('users')->whereNull('deleted_at')->count(),
        ];

        return Inertia::render('Admin/Users/Roles', [
            'roles' => $roles,
            'filters' => $request->only(['search']),
            'stats' => $stats,
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:40|unique:roles',
            'description' => 'nullable|string',
        ]);

        DB::table('roles')->insert([
            'name' => $validated['name'],
            'title' => $validated['name'], // Use name as title
            'description' => $validated['description'],
            'is_system' => false,
            'sort_order' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.users.roles')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, $id)
    {
        $role = DB::table('roles')->where('id', $id)->first();
        
        if (!$role) {
            return redirect()->back()->with('error', 'Role not found.');
        }

        $validated = $request->validate([
            'name' => "required|string|max:50|unique:roles,name,{$id}",
            'description' => 'nullable|string|max:255',
        ]);

        DB::table('roles')
            ->where('id', $id)
            ->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.users.roles')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role.
     */
    public function destroy($id)
    {
        $role = DB::table('roles')->where('id', $id)->first();

        if (!$role) {
            return redirect()->back()->with('error', 'Role not found.');
        }

        // Check if role has users
        $userCount = DB::table('users')->where('role_id', $id)->count();
        
        if ($userCount > 0) {
            return redirect()->back()->with('error', 'Cannot delete role that has users assigned.');
        }

        // Check if it's a default/system role
        $protectedRoles = ['admin', 'super-admin', 'Super Admin', 'Administrator'];
        if (in_array(strtolower($role->name), array_map('strtolower', $protectedRoles))) {
            return redirect()->back()->with('error', 'Cannot delete system role.');
        }

        DB::table('roles')->where('id', $id)->delete();

        return redirect()->route('admin.users.roles')
            ->with('success', 'Role deleted successfully.');
    }
}