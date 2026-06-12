<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'vendor_id'         => VendorFactory::new(),
            'category_id'       => ProductCategoryFactory::new(),
            'name'              => fake()->unique()->words(3, true),
            'short_description' => fake()->sentence(),
            'description'       => fake()->paragraphs(2, true),
            'type'              => 'ebook',
            'price'             => 100_000, // ₦1,000 in kobo
            'status'            => 'active',
            'approved_at'       => now(),
        ];
    }
}
