<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Graphics & Design',
                'icon' => 'bi-palette',
                'children' => [
                    'Logo Design', 'Social Media Graphics', 'Branding Kits',
                    'Flyers & Posters', 'UI Kits', 'Illustrations',
                ],
            ],
            [
                'name' => 'Digital Templates',
                'icon' => 'bi-file-earmark-ruled',
                'children' => [
                    'Presentation Templates', 'Spreadsheet Templates',
                    'Business Documents', 'Proposals & Pitch Decks',
                    'CV & Resume Templates',
                ],
            ],
            [
                'name' => 'Software & Tools',
                'icon' => 'bi-code-slash',
                'children' => [
                    'WordPress Plugins', 'Scripts & Automation',
                    'Web Application Source Code', 'Mobile App Source Code',
                    'Browser Extensions',
                ],
            ],
            [
                'name' => 'Education & Courses',
                'icon' => 'bi-mortarboard',
                'children' => [
                    'Video Courses', 'E-Books & Guides',
                    'Study Materials', 'Certification Prep',
                    'Business & Entrepreneurship',
                ],
            ],
            [
                'name' => 'Music & Audio',
                'icon' => 'bi-music-note-beamed',
                'children' => [
                    'Beats & Instrumentals', 'Sound Effects',
                    'Jingles', 'Podcast Intros', 'Gospel & Worship Music',
                ],
            ],
            [
                'name' => 'Photography & Video',
                'icon' => 'bi-camera',
                'children' => [
                    'Stock Photos', 'Stock Videos', 'Video Templates',
                    'Lightroom Presets', 'LUTs & Color Grading',
                ],
            ],
            [
                'name' => 'Business & Finance',
                'icon' => 'bi-briefcase',
                'children' => [
                    'Business Plans', 'Financial Models', 'Market Research',
                    'Legal Templates', 'HR & Operations',
                ],
            ],
            [
                'name' => 'Web & App Themes',
                'icon' => 'bi-layout-text-window',
                'children' => [
                    'WordPress Themes', 'HTML Templates',
                    'E-commerce Themes', 'Landing Pages',
                ],
            ],
        ];

        foreach ($categories as $sort => $cat) {
            $parent = ProductCategory::create([
                'name'       => $cat['name'],
                'icon'       => $cat['icon'],
                'is_active'  => true,
                'sort_order' => $sort + 1,
            ]);

            foreach ($cat['children'] as $childSort => $childName) {
                ProductCategory::create([
                    'parent_id'  => $parent->id,
                    'name'       => $childName,
                    'is_active'  => true,
                    'sort_order' => $childSort + 1,
                ]);
            }
        }
    }
}
