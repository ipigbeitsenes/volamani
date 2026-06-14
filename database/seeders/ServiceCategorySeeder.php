<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Creative Services',
                'icon' => 'bi-palette',
                'children' => [
                    'Graphic Design', 'Logo Design', 'UI/UX Design',
                    'Video Editing', 'Animation', 'Photography Services',
                ],
            ],
            [
                'name' => 'Tech & Development Services',
                'icon' => 'bi-code-slash',
                'children' => [
                    'Web Development', 'Mobile App Development', 'Software Engineering',
                    'API Integration', 'Bug Fixing', 'DevOps Services',
                ],
            ],
            [
                'name' => 'Marketing & Growth Services',
                'icon' => 'bi-graph-up-arrow',
                'children' => [
                    'SEO Services', 'Social Media Marketing', 'Paid Ads Management',
                    'Content Marketing', 'Influencer Marketing', 'Email Marketing',
                ],
            ],
            [
                'name' => 'Business Services',
                'icon' => 'bi-briefcase',
                'children' => [
                    'Business Consulting', 'Startup Advisory', 'Market Research',
                    'Financial Consulting', 'Business Registration Help',
                ],
            ],
            [
                'name' => 'Legal & Professional Services',
                'icon' => 'bi-bank',
                'children' => [
                    'Legal Consultation', 'Contract Drafting', 'Accounting Services',
                    'Tax Filing', 'Compliance Services',
                ],
            ],
            [
                'name' => 'Coaching & Education Services',
                'icon' => 'bi-mortarboard',
                'children' => [
                    'Online Tutoring', 'Career Coaching', 'Forex/Trading Mentorship',
                    'Business Coaching', 'Skill Training',
                ],
            ],
            [
                'name' => 'Technical & Field Services',
                'icon' => 'bi-wrench-adjustable',
                'children' => [
                    'Home Repair', 'Electrical Services', 'Plumbing',
                    'Construction', 'Car Repair',
                ],
            ],
            [
                'name' => 'Content & Writing Services',
                'icon' => 'bi-pencil-square',
                'children' => [
                    'Copywriting', 'Article Writing', 'Script Writing',
                    'Translation', 'Resume Writing',
                ],
            ],
        ];

        foreach ($categories as $sort => $cat) {
            $parent = ServiceCategory::create([
                'name'       => $cat['name'],
                'icon'       => $cat['icon'],
                'is_active'  => true,
                'sort_order' => $sort + 1,
            ]);

            foreach ($cat['children'] as $childSort => $childName) {
                ServiceCategory::create([
                    'parent_id'  => $parent->id,
                    'name'       => $childName,
                    'is_active'  => true,
                    'sort_order' => $childSort + 1,
                ]);
            }
        }
    }
}
