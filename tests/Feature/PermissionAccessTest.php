<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Database\Seeders\RoleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;

class PermissionAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the roles and permissions
        $this->seed([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
        ]);
    }

    public function test_admin_can_access_admin_routes()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id, 'email_verified_at' => now()]);

        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);
    }

    public function test_manager_can_access_manager_routes()
    {
        $managerRole = Role::where('name', 'manager')->first();
        $manager = User::factory()->create(['role_id' => $managerRole->id, 'email_verified_at' => now()]);

        $response = $this->actingAs($manager)->get('/manager/dashboard');
        $response->assertStatus(200);

        // Manager does not have admin role so they cannot access /admin/users
        $responseAdmin = $this->actingAs($manager)->get('/admin/users');
        $responseAdmin->assertStatus(403);
    }

    public function test_agent_can_access_agent_routes_but_not_admin()
    {
        $agentRole = Role::where('name', 'agent')->first();
        $agent = User::factory()->create(['role_id' => $agentRole->id, 'email_verified_at' => now()]);

        // Agent has view_ticket, so they can access agent dashboard
        $responseAgent = $this->actingAs($agent)->get('/agent/dashboard');
        $responseAgent->assertStatus(200);

        // Agent does NOT have manage_users, so they get 403 on admin routes
        $responseAdmin = $this->actingAs($agent)->get('/admin/users');
        $responseAdmin->assertStatus(403);
    }

    public function test_regular_user_cannot_access_manager_or_admin_routes()
    {
        $userRole = Role::where('name', 'user')->first();
        $user = User::factory()->create(['role_id' => $userRole->id, 'email_verified_at' => now()]);

        $responseAdmin = $this->actingAs($user)->get('/admin/users');
        $responseAdmin->assertStatus(403);

        $responseManager = $this->actingAs($user)->get('/manager/dashboard');
        $responseManager->assertStatus(403);
    }
}
