<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticatedRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_logged_in_user_hitting_login_is_sent_to_the_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/login')->assertRedirect(route('dashboard'));
        $this->actingAs($user)->get('/register')->assertRedirect(route('dashboard'));
    }

    public function test_guests_still_see_the_login_form(): void
    {
        $this->get('/login')->assertOk();
    }
}
