<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Factories\VendorFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_directory_lists_active_stores_for_guests(): void
    {
        $vendor = VendorFactory::new()->create(['business_name' => 'Pixel Forge Studio']);

        $this->get(route('vendors.index'))
            ->assertOk()
            ->assertSee('Browse Stores')
            ->assertSee('Pixel Forge Studio');
    }

    public function test_directory_can_be_searched(): void
    {
        VendorFactory::new()->create(['business_name' => 'Pixel Forge Studio']);
        VendorFactory::new()->create(['business_name' => 'GrowthLab Agency']);

        $this->get(route('vendors.index', ['q' => 'Pixel']))
            ->assertOk()
            ->assertSee('Pixel Forge Studio')
            ->assertDontSee('GrowthLab Agency');
    }

    public function test_a_buyer_can_follow_a_vendor_from_the_directory(): void
    {
        $vendor = VendorFactory::new()->create();
        $buyer = User::factory()->create();

        // The directory renders the follow control, and the toggle works.
        $this->actingAs($buyer)->get(route('vendors.index'))->assertOk();

        $this->actingAs($buyer)
            ->post(route('follow.toggle', $vendor))
            ->assertRedirect();

        $this->assertTrue($buyer->fresh()->isFollowing($vendor));
    }
}
