<?php

namespace App\Services\Subscription;

use App\Actions\Subscription\ActivateSubscriptionAction;
use App\Actions\Subscription\CancelSubscriptionAction;
use App\Actions\Subscription\RenewSubscriptionAction;
use App\Actions\Subscription\SubscribeAction;
use App\Enums\SubscriptionInvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Vendor;
use App\Repositories\Subscription\SubscriptionRepository;

class SubscriptionService
{
    public function __construct(
        private SubscribeAction $subscribeAction,
        private ActivateSubscriptionAction $activateAction,
        private CancelSubscriptionAction $cancelAction,
        private RenewSubscriptionAction $renewAction,
        private SubscriptionRepository $repo,
    ) {}

    // ─── Plan management (admin) ─────────────────────────────────────────────────

    public function createPlan(array $data): SubscriptionPlan
    {
        return SubscriptionPlan::create($data);
    }

    public function updatePlan(SubscriptionPlan $plan, array $data): SubscriptionPlan
    {
        $plan->update($data);

        return $plan->fresh();
    }

    public function togglePlan(SubscriptionPlan $plan): SubscriptionPlan
    {
        $plan->update(['is_active' => ! $plan->is_active]);

        return $plan->fresh();
    }

    /**
     * Permanently delete a plan. Refuses when any vendor is (or ever was)
     * subscribed to it, to avoid orphaning subscription records — deactivate
     * such a plan instead. Returns false when the delete is blocked.
     */
    public function deletePlan(SubscriptionPlan $plan): bool
    {
        if ($plan->subscriptions()->exists()) {
            return false;
        }

        // Hard delete: a deletable plan has never had a subscriber, so there is no
        // history to preserve, and this matches the "cannot be undone" confirm.
        $plan->forceDelete();

        return true;
    }

    // ─── Vendor lifecycle ────────────────────────────────────────────────────────

    public function subscribe(Vendor $vendor, SubscriptionPlan $plan, string $method = 'wallet'): array
    {
        return $this->subscribeAction->execute($vendor, $plan, $method);
    }

    public function cancel(Subscription $subscription): Subscription
    {
        return $this->cancelAction->execute($subscription);
    }

    /** Hook: a gateway payment for a subscription succeeded. */
    public function activateFromPayment(Payment $payment): ?Subscription
    {
        $subscription = $payment->payable;

        if (! $subscription instanceof Subscription) {
            return null;
        }

        if ($subscription->status !== SubscriptionStatus::Pending) {
            return $subscription; // idempotent
        }

        $invoice = $subscription->invoices()
            ->where('status', SubscriptionInvoiceStatus::Pending)
            ->latest()
            ->first();

        if (! $invoice) {
            return $subscription;
        }

        return $this->activateAction->execute($subscription, $invoice, 'paystack', $payment);
    }

    // ─── Scheduled billing ───────────────────────────────────────────────────────

    /**
     * Process every subscription whose paid period has lapsed: renew the ones set
     * to auto-renew, expire the rest. Returns a tally for the console output.
     *
     * @return array{renewed:int, expired:int, failed:int}
     */
    public function processBillingCycle(): array
    {
        $grace = (int) settings('subscription_grace_days', 3);
        $renewed = $expired = $failed = 0;

        foreach ($this->repo->dueForProcessing() as $subscription) {
            $renewable = $subscription->auto_renew
                && $subscription->billing_interval->isRecurring()
                && $subscription->status !== SubscriptionStatus::Cancelled;

            if (! $renewable) {
                $this->expire($subscription);
                $expired++;

                continue;
            }

            // Past-due beyond the grace window gives up and expires.
            if ($subscription->status === SubscriptionStatus::PastDue
                && $subscription->ends_at?->copy()->addDays($grace)->isPast()) {
                $this->expire($subscription);
                $expired++;

                continue;
            }

            if ($this->renewAction->execute($subscription)) {
                $renewed++;
            } else {
                $failed++;
            }
        }

        return ['renewed' => $renewed, 'expired' => $expired, 'failed' => $failed];
    }

    private function expire(Subscription $subscription): void
    {
        $subscription->update(['status' => SubscriptionStatus::Expired]);

        // Drop the vendor back to no plan if this was the one in effect.
        $vendor = $subscription->vendor;
        if ($vendor && $vendor->plan === $subscription->plan->slug) {
            $vendor->update(['plan' => null]);
        }
    }

    // ─── Query passthroughs ──────────────────────────────────────────────────────

    public function activePlans()
    {
        return $this->repo->activePlans();
    }

    public function allPlans()
    {
        return $this->repo->allPlans();
    }

    public function planBySlug(string $slug): ?SubscriptionPlan
    {
        return $this->repo->planBySlug($slug);
    }

    public function forAdmin(int $perPage = 20, array $filters = [])
    {
        return $this->repo->allForAdmin($perPage, $filters);
    }

    public function stats(): array
    {
        return $this->repo->stats();
    }
}
