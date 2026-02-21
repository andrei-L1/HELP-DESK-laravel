<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::all();
        $permissions = Permission::all()->keyBy('name');

        foreach ($roles as $role) {
            $rolePermissions = [];

            switch ($role->name) {
                case 'admin':
                    // Admin gets all permissions
                    $rolePermissions = $permissions->pluck('id')->toArray();
                    break;

                case 'manager':
                    // Manager can edit tickets and manage users but not delete
                    $rolePermissions = [
                        $permissions['view_ticket']->id ?? null,
                        $permissions['edit_ticket']->id ?? null,
                        $permissions['manage_users']->id ?? null,
                        $permissions['manage_roles']->id ?? null,
                    ];
                    break;

                case 'agent':
                    // Agent can view and edit tickets
                    $rolePermissions = [
                        $permissions['view_ticket']->id ?? null,
                        $permissions['edit_ticket']->id ?? null,
                    ];
                    break;

                case 'user':
                    // Regular user can create and view their tickets
                    $rolePermissions = [
                        $permissions['create_ticket']->id ?? null,
                        $permissions['view_ticket']->id ?? null,
                    ];
                    break;
            }

            // Filter out nulls (in case a permission doesn't exist)
            $rolePermissions = array_filter($rolePermissions);

            // Attach permissions
            $role->permissions()->sync($rolePermissions);
        }
    }
}