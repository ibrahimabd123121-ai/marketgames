<?php

namespace Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    public function test_guest_cannot_access_admin_endpoints(): void
    {
        $this->getJson('/api/admin/me')->assertUnauthorized();
        $this->postJson('/api/products', [])->assertUnauthorized();
    }

    public function test_customer_cannot_access_admin_endpoints_or_write_resources(): void
    {
        $customer = new User;
        $customer->setAttribute('role', 'customer');
        Sanctum::actingAs($customer);

        $this->getJson('/api/admin/me')->assertForbidden();
        $this->postJson('/api/products', [])->assertForbidden();
        $this->postJson('/api/categories', [])->assertForbidden();
    }

    public function test_admin_can_access_admin_identity_endpoint(): void
    {
        $admin = new User;
        $admin->setAttribute('id', 1);
        $admin->setAttribute('name', 'Admin');
        $admin->setAttribute('email', 'admin@example.com');
        $admin->setAttribute('role', 'admin');
        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/me')
            ->assertOk()
            ->assertJsonPath('data.role', 'admin');
    }
}
