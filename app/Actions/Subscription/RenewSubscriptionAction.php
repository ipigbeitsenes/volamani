<?php

namespace App\Actions\Subscription;

use App\Enums\SubscriptionInvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\TransactionType;
use App\Models\Subscription;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class RenewSubscriptionAction
{
    public function __construct(private WalletService $walletService) {}

    /**
     * Charge the next billing cycle from the vendor's wallet (the only source a
     * scheduled job can pull from). On success the period is extended; on a
     * shortfall the subscription drops to past-due to await funds or expiry.
     *
     * @return bool whether the renewal succeeded
     */
    public function execute(Subscription $subscription): bool
    {
        $wallet = $this->walletService->getOrCreate($subscription->user);

        if (! $wallet->canWithdraw($subscription->price)) {
            $this->markPastDue($subscription);

            return false;
        }

        DB::transaction(function () use ($subscription, $wallet) {
            $ledger = $this->walletService->debit(
                $wallet,
                $subscription->price,
                TransactionType::Debit,
                "Subscription renewal — {$subscription->plan->name} ({$subscription->reference})",
                $subscription,
            );

            $base = ($subscription->ends_at && $subscription->ends_at->isFuture())
                ? $subscription->ends_at
                : now();
            $end = $subscription->billing_interval->advance($base);
            $start = $subscription->ends_at ?? now();

            $subscription->update([
                'status' => SubscriptionStatus::Active,
                'ends_at' => $end,
                'last_payment_at' => now(),
            ]);

            $subscription->invoices()->create([
                'plan_id' => $subscription->plan_id,
                'amount' => $subscription->price,
                'status' => SubscriptionInvoiceStatus::Paid,
                'method' => 'wallet',
                'wallet_ledger_id' => $ledger->id,
                'period_start' => $start,
                'period_end' => $end,
                'paid_at' => now(),
            ]);
        });

        return true;
    }

    private function markPastDue(Subscription $subscription): void
    {
        $subscription->update(['status' => SubscriptionStatus::PastDue]);

        $subscription->invoices()->create([
            'plan_id' => $subscription->plan_id,
            'amount' => $subscription->price,
            'status' => SubscriptionInvoiceStatus::Failed,
            'method' => 'wallet',
        ]);
    }
}
