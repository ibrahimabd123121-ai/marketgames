<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_log_in_and_receive_a_sanctum_token(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => 'correct-password',
        ]);

        $response = $this->postJson('/api/admin/login', [
            'email' => $admin->email,
            'password' => 'correct-password',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $admin->id)
            ->assertJsonPath('data.user.email', $admin->email)
            ->assertJsonStructure([
                'data' => ['token', 'user' => ['id', 'name', 'email', 'role']],
            ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $admin->id,
            'name' => 'admin-auth',
        ]);
    }

    public function test_login_rejects_invalid_passwords(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => 'correct-password',
        ]);

        $this->postJson('/api/admin/login', [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ])->assertUnauthorized()
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_customers_cannot_use_the_admin_login_endpoint(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->postJson('/api/admin/login', [
            'email' => $customer->email,
            'password' => 'password',
        ])->assertUnauthorized()
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_requires_an_email_and_password(): void
    {
        $this->postJson('/api/admin/login')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }
}
