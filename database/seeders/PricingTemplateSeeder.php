<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PricingTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // Website Development
            ['category' => 'website', 'name' => 'Landing Page', 'pricing_type' => 'fixed', 'base_price' => 15000000, 'description' => 'Single-page marketing site', 'features' => ['Responsive design', '1 page', 'Contact form', 'Basic SEO'], 'sort_order' => 1],
            ['category' => 'website', 'name' => 'Business Website', 'pricing_type' => 'fixed', 'base_price' => 40000000, 'description' => '5-page corporate website', 'features' => ['5 pages', 'CMS', 'Contact form', 'SEO optimized', 'Mobile responsive'], 'sort_order' => 2],
            ['category' => 'website', 'name' => 'E-commerce Store', 'pricing_type' => 'fixed', 'base_price' => 80000000, 'description' => 'Full online store with payment integration', 'features' => ['Product catalog', 'Shopping cart', 'Paystack/Flutterwave', 'Admin panel', 'Inventory management'], 'sort_order' => 3],
            ['category' => 'website', 'name' => 'Custom Web App', 'pricing_type' => 'hourly', 'hourly_rate' => 1500000, 'min_hours' => 40, 'max_hours' => 200, 'description' => 'Custom web application development', 'features' => ['Custom features', 'Database design', 'API integration', 'Testing', 'Deployment'], 'sort_order' => 4],

            // Branding
            ['category' => 'branding', 'name' => 'Logo Design', 'pricing_type' => 'fixed', 'base_price' => 8000000, 'description' => 'Professional logo design', 'features' => ['3 concepts', '3 revisions', 'All file formats', 'Brand guidelines'], 'sort_order' => 1],
            ['category' => 'branding', 'name' => 'Brand Identity Package', 'pricing_type' => 'fixed', 'base_price' => 25000000, 'description' => 'Full brand identity system', 'features' => ['Logo design', 'Color palette', 'Typography', 'Business card', 'Letterhead', 'Brand guidelines'], 'sort_order' => 2],
            ['category' => 'branding', 'name' => 'Brand Strategy & Identity', 'pricing_type' => 'fixed', 'base_price' => 60000000, 'description' => 'Complete brand strategy and visual identity', 'features' => ['Brand audit', 'Positioning strategy', 'Full visual identity', 'Marketing collateral', 'Social media kit'], 'sort_order' => 3],

            // AI & Automation
            ['category' => 'ai_automation', 'name' => 'Chatbot Setup', 'pricing_type' => 'fixed', 'base_price' => 30000000, 'description' => 'AI-powered chatbot for customer service', 'features' => ['NLP training', 'Website integration', 'WhatsApp integration', 'Analytics dashboard'], 'sort_order' => 1],
            ['category' => 'ai_automation', 'name' => 'Business Process Automation', 'pricing_type' => 'hourly', 'hourly_rate' => 2000000, 'min_hours' => 20, 'max_hours' => 100, 'description' => 'Automate repetitive business processes', 'features' => ['Process audit', 'Workflow design', 'Integration setup', 'Testing & deployment', 'Training'], 'sort_order' => 2],
            ['category' => 'ai_automation', 'name' => 'AI Content System', 'pricing_type' => 'fixed', 'base_price' => 45000000, 'description' => 'Automated content generation and scheduling', 'features' => ['AI writing setup', 'Content calendar', 'Social media scheduling', 'SEO optimization', 'Performance tracking'], 'sort_order' => 3],

            // Software Development
            ['category' => 'software', 'name' => 'MVP Development', 'pricing_type' => 'milestone', 'base_price' => 120000000, 'description' => 'Minimum viable product for startups', 'features' => ['Requirements analysis', 'UI/UX design', 'Backend development', 'Testing', 'Deployment'], 'sort_order' => 1],
            ['category' => 'software', 'name' => 'Mobile App (Cross-platform)', 'pricing_type' => 'milestone', 'base_price' => 200000000, 'description' => 'iOS & Android app using Flutter/React Native', 'features' => ['UI/UX design', 'iOS & Android', 'Backend API', 'Push notifications', 'App store deployment'], 'sort_order' => 2],
            ['category' => 'software', 'name' => 'Custom Software', 'pricing_type' => 'hourly', 'hourly_rate' => 2500000, 'min_hours' => 80, 'max_hours' => 500, 'description' => 'Bespoke software solutions', 'features' => ['Architecture design', 'Development', 'Testing', 'Documentation', 'Maintenance plan'], 'sort_order' => 3],

            // Digital Marketing
            ['category' => 'digital_marketing', 'name' => 'SEO Package', 'pricing_type' => 'fixed', 'base_price' => 20000000, 'description' => '3-month SEO optimization campaign', 'features' => ['Keyword research', 'On-page SEO', 'Link building', 'Monthly reports', 'Google Analytics setup'], 'sort_order' => 1],
            ['category' => 'digital_marketing', 'name' => 'Paid Ads Management', 'pricing_type' => 'fixed', 'base_price' => 15000000, 'description' => 'Monthly Google/Facebook ads management', 'features' => ['Campaign setup', 'Ad creation', 'Audience targeting', 'A/B testing', 'Weekly reports'], 'sort_order' => 2],
            ['category' => 'digital_marketing', 'name' => 'Full Digital Strategy', 'pricing_type' => 'fixed', 'base_price' => 50000000, 'description' => 'Comprehensive 6-month digital marketing plan', 'features' => ['Market research', 'SEO', 'Paid ads', 'Email marketing', 'Content strategy', 'Monthly reporting'], 'sort_order' => 3],

            // Social Media
            ['category' => 'social_media', 'name' => 'Social Media Management (Basic)', 'pricing_type' => 'fixed', 'base_price' => 10000000, 'description' => '2 platforms, 12 posts/month', 'features' => ['2 platforms', '12 posts/month', 'Caption writing', 'Hashtag strategy', 'Monthly report'], 'sort_order' => 1],
            ['category' => 'social_media', 'name' => 'Social Media Management (Pro)', 'pricing_type' => 'fixed', 'base_price' => 25000000, 'description' => '4 platforms, daily posting + engagement', 'features' => ['4 platforms', '30 posts/month', 'Stories & reels', 'Community management', 'Ad campaigns', 'Detailed analytics'], 'sort_order' => 2],
            ['category' => 'social_media', 'name' => 'Influencer Campaign', 'pricing_type' => 'fixed', 'base_price' => 35000000, 'description' => 'Full influencer marketing campaign', 'features' => ['Influencer sourcing', 'Brief creation', 'Campaign management', 'Performance tracking', 'ROI report'], 'sort_order' => 3],

            // Agency Retainer
            ['category' => 'agency_retainer', 'name' => 'Starter Retainer', 'pricing_type' => 'fixed', 'base_price' => 30000000, 'description' => '20 hours/month agency support', 'features' => ['20 hours/month', 'Dedicated account manager', 'Monthly strategy call', 'Priority support'], 'sort_order' => 1],
            ['category' => 'agency_retainer', 'name' => 'Growth Retainer', 'pricing_type' => 'fixed', 'base_price' => 70000000, 'description' => '50 hours/month full-service agency', 'features' => ['50 hours/month', 'Full team access', 'Weekly calls', 'Marketing + development', 'Analytics dashboard'], 'sort_order' => 2],
            ['category' => 'agency_retainer', 'name' => 'Enterprise Retainer', 'pricing_type' => 'fixed', 'base_price' => 150000000, 'description' => 'Unlimited hours, full embedded agency team', 'features' => ['Unlimited hours', 'Dedicated team', 'Daily standups', 'All services included', 'SLA guarantee'], 'sort_order' => 3],

            // Consulting
            ['category' => 'consulting', 'name' => 'Strategy Session', 'pricing_type' => 'hourly', 'hourly_rate' => 2500000, 'min_hours' => 1, 'max_hours' => 4, 'description' => 'One-on-one business strategy consultation', 'features' => ['Business audit', 'Strategy roadmap', 'Action plan', 'Follow-up notes'], 'sort_order' => 1],
            ['category' => 'consulting', 'name' => 'Business Growth Package', 'pricing_type' => 'fixed', 'base_price' => 50000000, 'description' => '4-week intensive business transformation', 'features' => ['3 x 2hr sessions', 'Business audit', 'Growth strategy', 'Implementation roadmap', 'Monthly check-in'], 'sort_order' => 2],
            ['category' => 'consulting', 'name' => 'Fractional CMO/CTO', 'pricing_type' => 'fixed', 'base_price' => 100000000, 'description' => 'Part-time executive leadership (per month)', 'features' => ['20 hrs/month executive time', 'Strategy leadership', 'Team management', 'Stakeholder reporting', 'OKR setting'], 'sort_order' => 3],
        ];

        foreach ($templates as $template) {
            $template['features'] = json_encode($template['features']);
            DB::table('pricing_templates')->insertOrIgnore($template + [
                'hourly_rate' => $template['hourly_rate'] ?? 0,
                'min_hours' => $template['min_hours'] ?? 0,
                'max_hours' => $template['max_hours'] ?? 0,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $addOns = [
            // Universal add-ons (no category)
            ['category' => null, 'name' => 'Rush Delivery (48hrs)', 'description' => 'Priority queue, 48-hour turnaround', 'price' => 5000, 'is_percentage' => true, 'sort_order' => 1],
            ['category' => null, 'name' => 'Extended Warranty (6 months)', 'description' => 'Bug fixes and support for 6 months post-delivery', 'price' => 1500, 'is_percentage' => true, 'sort_order' => 2],
            ['category' => null, 'name' => 'NDA / Confidentiality Agreement', 'description' => 'Legal confidentiality protection', 'price' => 500000, 'is_percentage' => false, 'sort_order' => 3],
            ['category' => null, 'name' => 'Dedicated Project Manager', 'description' => 'Full-time PM assigned to your project', 'price' => 2000, 'is_percentage' => true, 'sort_order' => 4],

            // Website add-ons
            ['category' => 'website', 'name' => 'Extra Page', 'description' => 'Additional page design and development', 'price' => 3000000, 'is_percentage' => false, 'sort_order' => 1],
            ['category' => 'website', 'name' => 'Blog Setup', 'description' => 'Full blog with categories and CMS', 'price' => 8000000, 'is_percentage' => false, 'sort_order' => 2],
            ['category' => 'website', 'name' => 'WhatsApp Integration', 'description' => 'Click-to-chat WhatsApp button and chatbot', 'price' => 3000000, 'is_percentage' => false, 'sort_order' => 3],
            ['category' => 'website', 'name' => 'Google Analytics & SEO Setup', 'description' => 'Analytics, sitemap, meta tags, schema markup', 'price' => 5000000, 'is_percentage' => false, 'sort_order' => 4],
            ['category' => 'website', 'name' => 'Payment Integration', 'description' => 'Paystack or Flutterwave payment gateway', 'price' => 10000000, 'is_percentage' => false, 'sort_order' => 5],

            // Branding add-ons
            ['category' => 'branding', 'name' => 'Social Media Kit', 'description' => 'Branded templates for Instagram, Facebook, Twitter', 'price' => 5000000, 'is_percentage' => false, 'sort_order' => 1],
            ['category' => 'branding', 'name' => 'Pitch Deck Design', 'description' => '15-slide investor pitch deck', 'price' => 12000000, 'is_percentage' => false, 'sort_order' => 2],
            ['category' => 'branding', 'name' => 'Brand Video (Intro)', 'description' => '30-second branded animation/intro video', 'price' => 15000000, 'is_percentage' => false, 'sort_order' => 3],

            // Digital Marketing add-ons
            ['category' => 'digital_marketing', 'name' => 'Email Marketing Setup', 'description' => 'Mailchimp/ConvertKit setup + 3 email sequences', 'price' => 8000000, 'is_percentage' => false, 'sort_order' => 1],
            ['category' => 'digital_marketing', 'name' => 'Content Calendar (3 months)', 'description' => 'Planned content strategy for 3 months', 'price' => 5000000, 'is_percentage' => false, 'sort_order' => 2],
            ['category' => 'digital_marketing', 'name' => 'Competitor Analysis Report', 'description' => 'In-depth analysis of top 5 competitors', 'price' => 6000000, 'is_percentage' => false, 'sort_order' => 3],

            // Software add-ons
            ['category' => 'software', 'name' => 'UI/UX Design (Figma)', 'description' => 'Full wireframes and high-fidelity mockups', 'price' => 20000000, 'is_percentage' => false, 'sort_order' => 1],
            ['category' => 'software', 'name' => 'API Documentation', 'description' => 'Swagger/Postman API documentation', 'price' => 5000000, 'is_percentage' => false, 'sort_order' => 2],
            ['category' => 'software', 'name' => 'DevOps & Cloud Setup', 'description' => 'AWS/GCP setup, CI/CD pipeline, monitoring', 'price' => 15000000, 'is_percentage' => false, 'sort_order' => 3],
        ];

        foreach ($addOns as $addOn) {
            DB::table('pricing_add_ons')->insertOrIgnore($addOn + [
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
