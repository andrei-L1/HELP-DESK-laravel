<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(); // back to the forgot-password page with status

        // Assert that a reset token was created for this email
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ], 'sqlite');
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();

        // Create a reset token row directly in the test DB
        $token = 'test-token';
        DB::table('password_reset_tokens')->insert([
            'email'      => $user->email,
            'token'      => $token,
            'created_at' => now(),
        ]);

        $response = $this->get('/reset-password/'.$token.'?email='.$user->email);

        $response->assertStatus(200);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create();

        // Use the password broker to generate a real, hashed reset token
        $token = app('auth.password.broker')->createToken($user);

        $response = $this->post('/reset-password', [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password', $user->fresh()->password));
    }
}
