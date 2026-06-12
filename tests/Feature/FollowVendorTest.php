<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use Database\Factories\VendorFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowVendorTest extends TestCase
{
    use RefreshDatabase;

    private function vendor(): Vendor
    {
        return VendorFactory::new()->create();
    }

    public function test_a_user_can_follow_then_unfollow_a_vendor(): void
    {
        $vendor = $this->vendor();
        $buyer  = User::factory()->create();

        // Follow
        $this->actingAs($buyer)
            ->post(route('follow.toggle', $vendor))
            ->assertRedirect();

        $this->assertDatabaseHas('follows', [
            'follower_id' => $buyer->id,
            'vendor_id'   => $vendor->id,
        ]);
        $this->assertSame(1, $vendor->fresh()->followers_count);
        $this->assertTrue($buyer->fresh()->isFollowing($vendor));

        // Unfollow (toggle again)
        $this->actingAs($buyer)
            ->post(route('follow.toggle', $vendor))
            ->assertRedirect();

        $this->assertDatabaseMissing('follows', [
            'follower_id' => $buyer->id,
            'vendor_id'   => $vendor->id,
        ]);
        $this->assertSame(0, $vendor->fresh()->followers_count);
    }

    public function test_a_vendor_owner_cannot_follow_their_own_store(): void
    {
        $vendor = $this->vendor();

        $this->actingAs($vendor->user)
            ->post(route('follow.toggle', $vendor))
            ->assertRedirect();

        $this->assertDatabasemissing('follows', ['vendor_id' => $vendor->id]);
        $this->assertSame(0, $vendor->fresh()->followers_count);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $vendor = $this->vendor();

        $this->post(route('follow.toggle', $vendor))
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('follows', 0);
    }
}
