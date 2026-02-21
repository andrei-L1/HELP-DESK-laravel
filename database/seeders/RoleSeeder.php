<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name'        => 'user',
                'title'       => 'Regular User',
                'description' => 'Standard user with basic access',
                'is_system'   => true,
                'sort_order'  => 10,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'agent',
                'title'       => 'Agent',
                'description' => 'Can be assigned tickets and respond to users',
                'is_system'   => true,
                'sort_order'  => 15,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'manager',
                'title'       => 'Manager',
                'description' => 'Can manage content and users in their scope',
                'is_system'   => true,
                'sort_order'  => 20,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'admin',
                'title'       => 'Administrator',
                'description' => 'Full system access',
                'is_system'   => true,
                'sort_order'  => 30,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ];
        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                $role
            );
        }
    }
}