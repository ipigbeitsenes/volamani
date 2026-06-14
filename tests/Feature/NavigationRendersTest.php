<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke tests that the public home page and the buyer dashboard render without
 * error. Because these pages (and the shared navbar) reference many named
 * routes, a broken route() link throws and fails the test — cheap protection
 * for the navigation enhancements.
 */
class NavigationRendersTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_for_a_guest(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Explore the Marketplace')
            ->assertSee('Pricing Assistant');
    }

    public function test_buyer_dashboard_renders_with_full_navigation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Welcome back')
            ->assertSee('Support Tickets')
            ->assertSee('Service Orders');
    }
}
