<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Get all roles except 'admin'
        $roles = Role::where('name', '!=', 'admin')->get();

        foreach ($roles as $role) {
            User::create([
                'username' => strtolower($role->name) . "_user",
                'email' => strtolower($role->name) . "@example.com",
                'password' => Hash::make('password123'), // default password
                'first_name' => ucfirst($role->name) . "First",
                'last_name' => ucfirst($role->name) . "Last",
                'role_id' => $role->id,
                'is_active' => true,
                'email_verified' => true,
                'email_verified_at' => now(),
            ]);
        }
    }
}