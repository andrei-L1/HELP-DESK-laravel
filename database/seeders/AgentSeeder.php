<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;

class AgentSeeder extends Seeder
{
    public function run(): void
    {
        $agentRole = Role::where('name', 'agent')->first();
        if (!$agentRole) {
            $this->command->error('Agent role not found. Please run RoleSeeder first.');
            return;
        }

        $departments = Department::all();
        if ($departments->isEmpty()) {
            $this->command->error('No departments found. Please run DepartmentSeeder first.');
            return;
        }

        foreach ($departments as $department) {
            for ($i = 1; $i <= 5; $i++) {
                $uniqueSuffix = strtolower($department->short_code) . '_' . $i;
                $username = 'agent_' . $uniqueSuffix;
                $email = "agent.{$uniqueSuffix}@example.com";

                // Check or create agent
                $user = User::where('username', $username)->first();

                if (!$user) {
                    $userId = DB::table('users')->insertGetId([
                        'username'       => $username,
                        'email'          => $email,
                        'password'       => Hash::make('password123'),
                        'first_name'     => 'Agent ' . $i,
                        'last_name'      => $department->name,
                        'role_id'        => $agentRole->id,
                        'is_active'      => true,
                        'email_verified' => true,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                } else {
                    $userId = $user->id;
                }

                // Assign to department
                DB::table('user_departments')->updateOrInsert(
                    ['user_id' => $userId, 'department_id' => $department->id],
                    [
                        'is_primary' => true,
                        'joined_at'  => now(),
                    ]
                );
            }
        }

        $this->command->info('Agents seeded and assigned to their respective departments successfully!');
    }
}
