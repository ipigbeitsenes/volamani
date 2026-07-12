<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        foreach (['super-admin', 'admin', 'buyer', 'vendor', 'consultant', 'support', 'finance'] as $r) {
            Role::findOrCreate($r, 'web');
        }

        $admin = User::factory()->create();
        $admin->syncRoles(['super-admin', 'admin']);   // super-admin bypasses users.manage via Gate::before

        return $admin;
    }

    public function test_admin_can_assign_roles_to_a_user(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.users.roles', $user), ['roles' => ['vendor', 'support']])
            ->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->hasRole('vendor'));
        $this->assertTrue($user->hasRole('support'));
    }

    public function test_admin_cannot_assign_a_privileged_role(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.users.roles', $user), ['roles' => ['super-admin']])
            ->assertSessionHasErrors('roles.0');

        $this->assertFalse($user->fresh()->hasRole('super-admin'));
    }

    public function test_admin_can_verify_a_user(): void
    {
        $admin = $this->admin();
        $user = User::factory()->unverified()->create();
        $this->assertNull($user->email_verified_at);

        $this->actingAs($admin)
            ->put(route('admin.users.verify', $user))
            ->assertRedirect();

        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
