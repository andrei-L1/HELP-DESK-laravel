<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Technical Support', 'short_code' => 'TS'],
            ['name' => 'Billing', 'short_code' => 'BILL'],
            ['name' => 'Sales', 'short_code' => 'SALES'],
            ['name' => 'General', 'short_code' => 'GEN'],
        ];

        $managerRole = DB::table('roles')->where('name', 'manager')->first();

        foreach ($departments as $dept) {
            $managerUsername = strtolower($dept['short_code']) . '_manager';

            // Check or create manager
            $manager = DB::table('users')->where('username', $managerUsername)->first();
            if (!$manager) {
                $managerId = DB::table('users')->insertGetId([
                    'username' => $managerUsername,
                    'email' => strtolower($dept['short_code']) . '_manager@example.com',
                    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                    'first_name' => $dept['name'],
                    'last_name' => 'Manager',
                    'role_id' => $managerRole ? $managerRole->id : 3,
                    'is_active' => true,
                    'email_verified' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $managerId = $manager->id;
            }

            DB::table('departments')->updateOrInsert(
                ['short_code' => $dept['short_code']],
                array_merge($dept, [
                    'is_active'   => true,
                    'description' => null,
                    'manager_id'  => $managerId,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ])
            );

            $department = DB::table('departments')->where('short_code', $dept['short_code'])->first();

            DB::table('user_departments')->updateOrInsert(
                ['user_id' => $managerId, 'department_id' => $department->id],
                [
                    'is_primary' => true,
                    'joined_at' => now(),
                ]
            );
        }

        $this->command->info('Departments and their managers seeded.');
    }
}
