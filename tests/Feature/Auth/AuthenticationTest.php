<?php

namespace Tests\Feature\Auth;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_customer_login_ignores_a_previous_admin_only_url(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);
        $customerUser = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);
        Customer::create([
            'user_id' => $customerUser->id,
            'customer_code' => 'CU-1001',
            'full_name' => 'Customer User',
            'phone' => '0500000001',
            'country' => 'UAE',
            'customer_type' => 'both',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $response = $this
            ->withSession(['url.intended' => route('users.index')])
            ->post('/login', [
                'email' => $customerUser->email,
                'password' => 'password',
            ]);

        $response->assertRedirect(route('dashboard'));

        $this->actingAs($customerUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('My Dashboard');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
