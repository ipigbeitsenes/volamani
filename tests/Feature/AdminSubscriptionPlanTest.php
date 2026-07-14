<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\Subscription\SubscriptionService;
use Database\Factories\VendorFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSubscriptionPlanTest extends TestCase
{
    use RefreshDatabase;

    private function plan(): SubscriptionPlan
    {
        return SubscriptionPlan::create([
            'name' => 'Test Plan',
            'slug' => 'test-plan-'.uniqid(),
            'price' => 250_000,
            'billing_interval' => 'monthly',
            'is_active' => true,
            'sort_order' => 9,
        ]);
    }

    public function test_a_plan_with_no_subscribers_can_be_deleted(): void
    {
        $plan = $this->plan();

        $this->assertTrue(app(SubscriptionService::class)->deletePlan($plan));
        $this->assertDatabaseMissing('subscription_plans', ['id' => $plan->id]);
    }

    public function test_a_plan_with_subscribers_is_protected_from_deletion(): void
    {
        $plan = $this->plan();
        $vendor = VendorFactory::new()->create();

        Subscription::create([
            'reference' => 'SUB-TEST-1',
            'vendor_id' => $vendor->id,
            'user_id' => $vendor->user_id,
            'plan_id' => $plan->id,
            'price' => $plan->price,
            'billing_interval' => 'monthly',
            'status' => 'active',
            'starts_at' => now(),
        ]);

        $this->assertFalse(app(SubscriptionService::class)->deletePlan($plan));
        $this->assertDatabaseHas('subscription_plans', ['id' => $plan->id]);
    }
}
