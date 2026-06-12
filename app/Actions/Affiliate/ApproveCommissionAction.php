<?php

namespace App\Actions\Affiliate;

use App\Enums\CommissionStatus;
use App\Enums\TransactionType;
use App\Models\AffiliateCommission;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class ApproveCommissionAction
{
    public function __construct(private WalletService $walletService) {}

    /**
     * Approve a commission and credit it to the affiliate's wallet in one step.
     * Idempotent — a commission already paid is returned untouched.
     */
    public function execute(AffiliateCommission $commission): AffiliateCommission
    {
        if ($commission->isPaid() || $commission->status === CommissionStatus::Cancelled) {
            return $commission;
        }

        return DB::transaction(function () use ($commission) {
            $account = $commission->account;
            $wallet  = $this->walletService->getOrCreate($account->user);

            $ledger = $this->walletService->credit(
                $wallet,
                $commission->amount,
                TransactionType::AffiliateEarning,
                "{$commission->type->label()} — {$commission->reference}",
                $commission,
            );

            $commission->update([
                'status'           => CommissionStatus::Paid,
                'wallet_ledger_id' => $ledger->id,
                'approved_at'      => $commission->approved_at ?? now(),
                'paid_at'          => now(),
            ]);

            $account->increment('total_paid', $commission->amount);

            return $commission->fresh();
        });
    }

    /** Reject a pending commission without paying it out. */
    public function cancel(AffiliateCommission $commission, ?string $reason = null): AffiliateCommission
    {
        if ($commission->isPaid()) {
            return $commission;
        }

        return DB::transaction(function () use ($commission, $reason) {
            // Earned-but-unpaid amount no longer counts toward lifetime earnings.
            $commission->account->decrement('total_earned', $commission->amount);

            $commission->update([
                'status' => CommissionStatus::Cancelled,
                'note'   => $reason ?: $commission->note,
            ]);

            return $commission->fresh();
        });
    }
}
