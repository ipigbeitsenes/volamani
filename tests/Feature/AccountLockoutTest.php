<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Brute-force protection: 5 failed logins lock the account; the lock holds
 * even when the correct password is then supplied.
 */
class AccountLockoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_five_failed_attempts_lock_the_account(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Correct-h0rse')]);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.post'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $user->refresh();
        $this->assertTrue($user->isLocked());
        $this->assertSame(5, (int) $user->failed_login_attempts);

        // Correct password while locked must still be rejected.
        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'Correct-h0rse',
        ])->assertSessionHasErrors();

        $this->assertGuest();
    }

    public function test_successful_login_clears_the_failure_counter(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Correct-h0rse')]);

        for ($i = 0; $i < 3; $i++) {
            $this->post(route('login.post'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }
        $this->assertSame(3, (int) $user->fresh()->failed_login_attempts);

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'Correct-h0rse',
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertSame(0, (int) $user->fresh()->failed_login_attempts);
        $this->assertFalse($user->fresh()->isLocked());
    }

    public function test_failed_logins_are_recorded_in_the_security_log(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertDatabaseHas('security_logs', [
            'user_id' => $user->id,
            'event' => 'login_failed',
        ]);
    }
}
