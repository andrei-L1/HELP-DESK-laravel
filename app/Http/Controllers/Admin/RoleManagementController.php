<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Role;
use App\Models\Permission;

class RoleManagementController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        $query = Role::with('permissions')
            ->withCount(['users' => function($q) {
                $q->whereNull('deleted_at');
            }]);

        // Apply search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Order by
        $query->orderBy('name');

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
                    'permissions' => $role->permissions->pluck('id')->toArray(),
                ];
            });

        // Get stats
        $stats = [
            'total_roles' => Role::count(),
            'total_users' => DB::table('users')->whereNull('deleted_at')->count(),
        ];

        return Inertia::render('Admin/Users/Roles', [
            'roles' => $roles,
            'permissions' => Permission::orderBy('category')->orderBy('name')->get(),
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
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'title' => $validated['name'], // Use name as title
            'description' => $validated['description'],
            'is_system' => false,
            'sort_order' => 100,
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('admin.users.roles')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        
        $validated = $request->validate([
            'name' => "required|string|max:50|unique:roles,name,{$id}",
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
        ]);

        if (array_key_exists('permissions', $validated)) {
            $role->permissions()->sync($validated['permissions'] ?? []);
        }

        return redirect()->route('admin.users.roles')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role.
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

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

        $role->delete();

        return redirect()->route('admin.users.roles')
            ->with('success', 'Role deleted successfully.');
    }
}