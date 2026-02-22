<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,             // seed roles first
            PermissionSeeder::class,       // then seed permissions
            RolePermissionSeeder::class,   // then link roles & permissions
            AdminUserSeeder::class,        // create admin user
            TicketStatusSeeder::class,
            TicketPrioritySeeder::class,
            DepartmentSeeder::class,
            AgentSeeder::class,
            TicketCategorySeeder::class,
            SlaPolicySeeder::class,
            TicketSeeder::class,
        ]);
    }
}