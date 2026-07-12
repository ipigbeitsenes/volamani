<?php

namespace App\Actions\Affiliate;

use App\Enums\AffiliateStatus;
use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use App\Enums\ReferralStatus;
use App\Models\AffiliateCommission;
use App\Models\ConsultationSession;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Referral;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RecordConversionAction
{
    public function __construct(
        private ApproveCommissionAction $approveAction,
        private EnrollAffiliateAction $enrollAction,
    ) {}

    /**
     * On a successful payment, reward referrers a percentage of the PLATFORM'S
     * commission on that transaction:
     *   - the BUYER's referrer earns on every purchase the buyer makes;
     *   - the VENDOR's referrer earns on every sale the vendor makes.
     * Commissions are generated per-transaction (ongoing, not one-off) and paid
     * straight to the referrer's wallet.
     *
     * @return AffiliateCommission[] commissions created for this payment (0–2)
     */
    public function execute(Payment $payment): array
    {
        if (! settings('affiliate_enabled', true)) {
            return [];
        }

        $details = $this->resolve($payment->payable);
        if (! $details || $details['fee'] <= 0) {
            return [];
        }

        $created = [];

        foreach (['buyer' => $details['buyer_id'], 'vendor' => $details['vendor_user_id']] as $role => $referredUserId) {
            if (! $referredUserId) {
                continue;
            }

            $commission = $this->rewardReferrerOf($referredUserId, $role, $payment, $details['fee']);
            if ($commission) {
                $created[] = $commission;
            }
        }

        return $created;
    }

    /**
     * Reward the person who referred $referredUserId with a slice of the
     * platform commission ($fee). Idempotent per (referral, payment).
     */
    private function rewardReferrerOf(int $referredUserId, string $role, Payment $payment, int $fee): ?AffiliateCommission
    {
        $referral = $this->resolveReferral($referredUserId);
        if (! $referral) {
            return null;
        }

        $account = $referral->account;
        if (! $account || $account->status !== AffiliateStatus::Active) {
            return null;
        }

        // The referrer can't earn off their own purchase/sale.
        if ($account->user_id === $referredUserId) {
            return null;
        }

        // One commission per referral per payment.
        $exists = AffiliateCommission::where('referral_id', $referral->id)
            ->where('earnable_type', Payment::class)
            ->where('earnable_id', $payment->id)
            ->exists();
        if ($exists) {
            return null;
        }

        $rate = $account->effectiveRate();
        $amount = (int) round($fee * $rate / 100);
        if ($amount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($account, $referral, $payment, $amount, $rate, $role, $referredUserId) {
            $commission = AffiliateCommission::create([
                'affiliate_account_id' => $account->id,
                'referral_id' => $referral->id,
                'earnable_type' => Payment::class,
                'earnable_id' => $payment->id,
                'buyer_id' => $referredUserId,
                'type' => CommissionType::SaleCommission,
                'amount' => $amount,
                'rate_applied' => $rate,
                'status' => CommissionStatus::Pending,
                'note' => ucfirst($role)." referral — {$rate}% of platform commission on {$payment->reference}",
            ]);

            $account->increment('total_earned', $amount);
            $account->increment('conversions_count');

            if ($referral->status !== ReferralStatus::Rewarded) {
                $referral->update([
                    'status' => ReferralStatus::Rewarded,
                    'qualified_at' => $referral->qualified_at ?? now(),
                    'rewarded_at' => now(),
                ]);
            }

            // Pay it straight to the referrer's wallet.
            $this->approveAction->execute($commission);

            return $commission->fresh();
        });
    }

    /**
     * Find the referral for a user, creating it on the fly (and auto-enrolling
     * the referrer) when the user has a referred_by but no referral row yet —
     * so referrals work even if the referrer never explicitly joined.
     */
    private function resolveReferral(int $referredUserId): ?Referral
    {
        $referral = Referral::with('account')->where('referred_user_id', $referredUserId)->first();
        if ($referral) {
            return $referral;
        }

        $user = User::find($referredUserId);
        if (! $user || ! $user->referred_by) {
            return null;
        }

        $referrer = User::find($user->referred_by);
        if (! $referrer) {
            return null;
        }

        $account = $this->enrollAction->execute($referrer);

        return Referral::create([
            'affiliate_account_id' => $account->id,
            'referrer_id' => $referrer->id,
            'referred_user_id' => $user->id,
            'status' => ReferralStatus::Pending,
            'signup_reward' => 0,
        ])->setRelation('account', $account);
    }

    /**
     * Extract [platform fee, buyer id, vendor's user id] from the payable so we
     * can reward both the buyer's and the seller's referrer.
     */
    private function resolve(?Model $payable): ?array
    {
        return match (true) {
            $payable instanceof Order => [
                'fee' => (int) $payable->platform_fee,
                'buyer_id' => $payable->buyer_id,
                'vendor_user_id' => $payable->vendor?->user_id,
            ],
            $payable instanceof ServiceOrder => [
                'fee' => (int) $payable->platform_fee,
                'buyer_id' => $payable->buyer_id,
                'vendor_user_id' => $payable->vendor?->user_id,
            ],
            $payable instanceof ConsultationSession => [
                'fee' => (int) $payable->platform_fee,
                'buyer_id' => $payable->buyer_id,
                'vendor_user_id' => $payable->profile?->vendor?->user_id,
            ],
            default => null,
        };
    }
}
