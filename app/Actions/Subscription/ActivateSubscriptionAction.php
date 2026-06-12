<?php

namespace App\Actions\Subscription;

use App\Enums\SubscriptionInvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\WalletLedger;
use Illuminate\Support\Facades\DB;

class ActivateSubscriptionAction
{
    /**
     * Mark a subscription paid-and-active for a fresh billing period, settle the
     * matching invoice, and stamp the plan onto the vendor. Used by every paid
     * path (wallet, gateway, free) — idempotent on an already-active period.
     */
    public function execute(
        Subscription        $subscription,
        SubscriptionInvoice $invoice,
        string              $method,
        ?Payment            $payment = null,
        ?WalletLedger       $ledger = null,
    ): Subscription {
        return DB::transaction(function () use ($subscription, $invoice, $method, $payment, $ledger) {
            $now   = now();
            $start = $subscription->starts_at ?? $now;
            $end   = $subscription->billing_interval->advance($now); // null = lifetime

            $subscription->update([
                'status'          => SubscriptionStatus::Active,
                'starts_at'       => $start,
                'ends_at'         => $end,
                'last_payment_at' => $now,
            ]);

            $invoice->update([
                'status'           => SubscriptionInvoiceStatus::Paid,
                'method'           => $method,
                'payment_id'       => $payment?->id,
                'wallet_ledger_id' => $ledger?->id,
                'period_start'     => $start,
                'period_end'       => $end,
                'paid_at'          => $now,
            ]);

            // Reflect the active plan on the vendor for quick reads / featured logic.
            $subscription->vendor->update([
                'plan'        => $subscription->plan->slug,
                'is_featured' => $subscription->plan->featured_listing ? true : $subscription->vendor->is_featured,
            ]);

            return $subscription->fresh();
        });
    }
}
