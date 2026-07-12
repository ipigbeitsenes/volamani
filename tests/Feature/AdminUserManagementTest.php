<?php

namespace Tests\Feature;

use App\Enums\Status;
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

    public function test_granting_vendor_role_creates_an_active_store(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.users.roles', $user), ['roles' => ['vendor']])
            ->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->hasRole('vendor'));
        $this->assertNotNull($user->vendor, 'A vendor store should be created.');
        $this->assertSame(Status::Active, $user->vendor->status);
    }

    public function test_removing_vendor_role_takes_the_store_offline(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        $this->actingAs($admin)->put(route('admin.users.roles', $user), ['roles' => ['vendor']]);
        $this->actingAs($admin)->put(route('admin.users.roles', $user), ['roles' => ['buyer']]);

        $user->refresh();
        $this->assertFalse($user->hasRole('vendor'));
        $this->assertSame(Status::Inactive, $user->vendor->status);
    }
}
