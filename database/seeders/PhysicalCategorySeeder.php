<?php

namespace Database\Seeders;

use App\Models\PhysicalCategory;
use Illuminate\Database\Seeder;

class PhysicalCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Fashion & Apparel',
                'icon' => 'bi-bag-heart',
                'children' => [
                    "Men's Fashion", "Women's Fashion", 'Kids & Baby Clothing',
                    'Shoes & Footwear', 'Bags & Luggage', 'Jewelry & Watches',
                    'Traditional Wear', 'Sportswear & Activewear', 'Accessories',
                ],
            ],
            [
                'name' => 'Electronics & Gadgets',
                'icon' => 'bi-cpu',
                'children' => [
                    'Smartphones & Tablets', 'Computers & Laptops', 'Computer Accessories',
                    'Smart Devices & IoT', 'Audio & Headphones', 'Gaming Consoles',
                    'Cameras & Photography', 'Wearables',
                ],
            ],
            [
                'name' => 'Home, Living & Furniture',
                'icon' => 'bi-house-door',
                'children' => [
                    'Furniture', 'Home Decor', 'Kitchen & Dining', 'Bedding & Mattresses',
                    'Lighting', 'Cleaning Supplies', 'Storage & Organization', 'Garden & Outdoor',
                ],
            ],
            [
                'name' => 'Beauty & Personal Care',
                'icon' => 'bi-droplet',
                'children' => [
                    'Skincare', 'Makeup', 'Hair Care', 'Fragrance & Perfume',
                    'Beauty Tools', 'Personal Hygiene',
                ],
            ],
            [
                'name' => 'Health & Wellness',
                'icon' => 'bi-heart-pulse',
                'children' => [
                    'Supplements', 'Fitness Equipment', 'Medical Supplies',
                    'Herbal Products', 'Wellness Devices',
                ],
            ],
            [
                'name' => 'Baby, Kids & Toys',
                'icon' => 'bi-balloon',
                'children' => [
                    'Baby Clothing', 'Baby Products', 'Toys & Games',
                    'Educational Toys', 'Nursery Items',
                ],
            ],
            [
                'name' => 'Food & Grocery',
                'icon' => 'bi-basket',
                'children' => [
                    'Fresh Foods', 'Packaged Foods', 'Drinks & Beverages',
                    'Snacks', 'Organic Foods',
                ],
            ],
            [
                'name' => 'Automotive & Tools',
                'icon' => 'bi-tools',
                'children' => [
                    'Car Parts', 'Motorbike Parts', 'Car Accessories',
                    'Tools & Equipment', 'Industrial Supplies',
                ],
            ],
            [
                'name' => 'Sports & Outdoor',
                'icon' => 'bi-bicycle',
                'children' => [
                    'Gym Equipment', 'Camping Gear', 'Cycling', 'Fishing', 'Sports Equipment',
                ],
            ],
        ];

        foreach ($categories as $sort => $cat) {
            $parent = PhysicalCategory::create([
                'name' => $cat['name'],
                'icon' => $cat['icon'],
                'is_active' => true,
                'sort_order' => $sort + 1,
            ]);

            foreach ($cat['children'] as $childSort => $childName) {
                PhysicalCategory::create([
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'is_active' => true,
                    'sort_order' => $childSort + 1,
                ]);
            }
        }
    }
}
