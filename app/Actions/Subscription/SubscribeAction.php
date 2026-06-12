<?php

namespace App\Actions\Subscription;

use App\Enums\SubscriptionInvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\TransactionType;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\Vendor;
use App\Services\Payment\PaymentService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class SubscribeAction
{
    public function __construct(
        private ActivateSubscriptionAction $activateAction,
        private WalletService              $walletService,
    ) {}

    /**
     * Subscribe a vendor to a plan.
     *
     * @return array{status:string, url?:string, subscription?:\App\Models\Subscription}
     *   status ∈ active | trialing | redirect | insufficient | exists
     */
    public function execute(Vendor $vendor, SubscriptionPlan $plan, string $method = 'wallet'): array
    {
        // Already on this exact plan and still entitled — nothing to do.
        $current = $vendor->activeSubscription();
        if ($current && $current->plan_id === $plan->id) {
            return ['status' => 'exists', 'subscription' => $current];
        }

        $chargesWallet = ! $plan->isFree() && ! $plan->hasTrial() && $method === 'wallet';

        // Pre-flight the wallet balance outside the transaction so a shortfall
        // doesn't leave a half-built subscription behind.
        if ($chargesWallet) {
            $wallet = $this->walletService->getOrCreate($vendor->user);
            if (! $wallet->canWithdraw($plan->price)) {
                return ['status' => 'insufficient'];
            }
        }

        return DB::transaction(function () use ($vendor, $plan, $method, $current) {
            // Supersede any prior active subscription (upgrade / downgrade / switch).
            if ($current) {
                $current->update([
                    'status'       => SubscriptionStatus::Expired,
                    'auto_renew'   => false,
                    'ends_at'      => now(),
                    'cancelled_at' => $current->cancelled_at ?? now(),
                ]);
            }

            $subscription = $vendor->subscriptions()->create([
                'user_id'          => $vendor->user_id,
                'plan_id'          => $plan->id,
                'price'            => $plan->price,
                'billing_interval' => $plan->billing_interval->value,
                'status'           => SubscriptionStatus::Pending,
                'auto_renew'       => $plan->billing_interval->isRecurring(),
                'starts_at'        => now(),
            ]);

            $invoice = $this->openInvoice($subscription, $plan->price);

            // ── Free plan ──────────────────────────────────────────────────────
            if ($plan->isFree()) {
                $this->activateAction->execute($subscription, $invoice, 'free');

                return ['status' => 'active', 'subscription' => $subscription->fresh()];
            }

            // ── Free trial ─────────────────────────────────────────────────────
            if ($plan->hasTrial()) {
                $trialEnds = now()->addDays($plan->trial_days);

                $subscription->update([
                    'status'        => SubscriptionStatus::Trialing,
                    'trial_ends_at' => $trialEnds,
                    'ends_at'       => $trialEnds,
                ]);

                $invoice->update([
                    'status'       => SubscriptionInvoiceStatus::Void,
                    'method'       => 'trial',
                    'period_start' => now(),
                    'period_end'   => $trialEnds,
                ]);

                $vendor->update(['plan' => $plan->slug]);

                return ['status' => 'trialing', 'subscription' => $subscription->fresh()];
            }

            // ── Wallet charge ──────────────────────────────────────────────────
            if ($method === 'wallet') {
                $wallet = $this->walletService->getOrCreate($vendor->user);

                $ledger = $this->walletService->debit(
                    $wallet,
                    $plan->price,
                    TransactionType::Debit,
                    "Subscription — {$plan->name} ({$subscription->reference})",
                    $subscription,
                );

                $this->activateAction->execute($subscription, $invoice, 'wallet', null, $ledger);

                return ['status' => 'active', 'subscription' => $subscription->fresh()];
            }

            // ── Gateway (Paystack) ─────────────────────────────────────────────
            // PaymentService resolved lazily to avoid a circular container
            // dependency (PaymentService → VerifyPaymentAction → SubscriptionService
            // → SubscribeAction → PaymentService).
            $result = app(PaymentService::class)->initiatePaystackPayment(
                $vendor->user,
                $plan->price,
                $subscription,
                ['payable_type' => 'subscription'],
            );

            return ['status' => 'redirect', 'url' => $result['authorization_url'], 'subscription' => $subscription->fresh()];
        });
    }

    private function openInvoice($subscription, int $amount): SubscriptionInvoice
    {
        return $subscription->invoices()->create([
            'plan_id' => $subscription->plan_id,
            'amount'  => $amount,
            'status'  => SubscriptionInvoiceStatus::Pending,
        ]);
    }
}
