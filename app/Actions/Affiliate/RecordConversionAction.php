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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RecordConversionAction
{
    public function __construct(private ApproveCommissionAction $approveAction) {}

    /**
     * On a referred user's first qualifying purchase, generate a sale commission
     * for their referrer and mark the referral rewarded. One reward per referred
     * user (the referral leaves the Pending state on first conversion).
     */
    public function execute(Payment $payment): ?AffiliateCommission
    {
        if (! settings('affiliate_enabled', true)) {
            return null;
        }

        $payable = $payment->payable;
        $buyerId = $this->buyerIdFor($payable);
        if (! $buyerId) {
            return null;
        }

        $referral = Referral::where('referred_user_id', $buyerId)
            ->where('status', ReferralStatus::Pending)
            ->first();

        if (! $referral) {
            return null; // not a referred buyer, or already rewarded
        }

        $account = $referral->account;
        if (! $account || $account->status !== AffiliateStatus::Active) {
            return null;
        }

        return DB::transaction(function () use ($account, $referral, $payment, $payable, $buyerId) {
            $rate   = $account->effectiveRate();
            $amount = (int) round($payment->amount * $rate / 100);

            $referral->update([
                'status'       => ReferralStatus::Rewarded,
                'qualified_at' => now(),
                'rewarded_at'  => now(),
            ]);

            $account->increment('conversions_count');

            if ($amount <= 0) {
                return null;
            }

            $commission = AffiliateCommission::create([
                'affiliate_account_id' => $account->id,
                'referral_id'          => $referral->id,
                'earnable_type'        => $payable::class,
                'earnable_id'          => $payable->getKey(),
                'buyer_id'             => $buyerId,
                'type'                 => CommissionType::SaleCommission,
                'amount'               => $amount,
                'rate_applied'         => $rate,
                'status'               => CommissionStatus::Pending,
                'note'                 => "Commission on payment {$payment->reference}",
            ]);

            $account->increment('total_earned', $amount);

            if (settings('affiliate_auto_approve', false)) {
                $this->approveAction->execute($commission);
            }

            return $commission;
        });
    }

    private function buyerIdFor(?Model $payable): ?int
    {
        return match (true) {
            $payable instanceof Order               => $payable->buyer_id,
            $payable instanceof ServiceOrder        => $payable->buyer_id,
            $payable instanceof ConsultationSession => $payable->buyer_id,
            default                                 => null,
        };
    }
}
