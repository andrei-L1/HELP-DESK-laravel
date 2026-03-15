<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;

class RegularUserSeeder extends Seeder
{
    public function run(): void
    {
        $userRole = Role::where('name', 'user')->first();
        if (!$userRole) {
            $this->command->error('User role not found. Please run RoleSeeder first.');
            return;
        }

        for ($i = 1; $i <= 20; $i++) {
            $username = 'user_' . $i;
            $email = "user{$i}@example.com";

            // Check or create user
            $user = User::where('username', $username)->first();

            if (!$user) {
                DB::table('users')->insert([
                    'username'       => $username,
                    'email'          => $email,
                    'password'       => Hash::make('password123'),
                    'first_name'     => 'Regular',
                    'last_name'      => 'User ' . $i,
                    'role_id'        => $userRole->id,
                    'is_active'      => true,
                    'email_verified' => true,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }

        $this->command->info('Regular users seeded successfully!');
    }
}
