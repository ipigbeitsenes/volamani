<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'business_name' => fake()->unique()->company(),
            'status'        => 'active',
            'approved_at'   => now(),
        ];
    }
}
