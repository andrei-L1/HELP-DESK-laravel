<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'create_ticket', 'title' => 'Create Ticket', 'category' => 'tickets', 'created_at' => now()],
            ['name' => 'view_ticket', 'title' => 'View Ticket', 'category' => 'tickets', 'created_at' => now()],
            ['name' => 'edit_ticket', 'title' => 'Edit Ticket', 'category' => 'tickets', 'created_at' => now()],
            ['name' => 'delete_ticket', 'title' => 'Delete Ticket', 'category' => 'tickets', 'created_at' => now()],
            ['name' => 'manage_users', 'title' => 'Manage Users', 'category' => 'users', 'created_at' => now()],
            ['name' => 'manage_roles', 'title' => 'Manage Roles', 'category' => 'roles', 'created_at' => now()],
            ['name' => 'manage_permissions', 'title' => 'Manage Permissions', 'category' => 'permissions', 'created_at' => now()],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}