<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'username'       => 'admin',
            'email'          => 'admin@example.com',
            'password'       => Hash::make('Admin@123'), // set a secure password
            'first_name'     => 'System',
            'last_name'      => 'Administrator',
            'display_name'   => 'Admin',
            'role_id'        => 3, // admin role
            'phone'          => '09123456789',
            'timezone'       => 'Asia/Manila',
            'is_active'      => true,
            'email_verified' => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }
}
