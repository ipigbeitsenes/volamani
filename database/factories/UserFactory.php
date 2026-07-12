<?php

namespace Database\Factories;

use App\Enums\KYCStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'kyc_status' => KYCStatus::Unverified,
            'referral_code' => strtoupper(Str::random(8)),

            // Real users accept the Terms at registration, so factory users do too
            // — otherwise EnsureTermsAccepted redirects them off every gated web
            // route. Use ->unacceptedTerms() to exercise the acceptance gate itself.
            'terms_accepted_at' => now(),
            'terms_version' => config('legal.terms_version', '1'),
        ];
    }

    /** A user who has not yet accepted the current Terms (exercises the gate). */
    public function unacceptedTerms(): static
    {
        return $this->state(fn (array $attributes) => [
            'terms_accepted_at' => null,
            'terms_version' => null,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
