<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_user_management_page(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Users & Roles');
    }

    public function test_manager_cannot_access_user_management_page(): void
    {
        $manager = User::factory()->create([
            'role' => User::ROLE_MANAGER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($manager)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_a_user_account(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Store Manager',
                'email' => 'manager@example.com',
                'role' => User::ROLE_MANAGER,
                'status' => User::STATUS_ACTIVE,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'manager@example.com',
            'role' => User::ROLE_MANAGER,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($admin)
            ->put(route('users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_INACTIVE,
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'status' => User::STATUS_INACTIVE,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }
}
