<?php

namespace App\Actions\Affiliate;

use App\Enums\AffiliateStatus;
use App\Enums\CommissionStatus;
use App\Enums\CommissionType;
use App\Enums\ReferralStatus;
use App\Models\AffiliateCommission;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RecordReferralSignupAction
{
    public function __construct(private ApproveCommissionAction $approveAction) {}

    /**
     * When a newly registered user was referred (users.referred_by) and the
     * referrer runs an active affiliate account, open a Referral record and —
     * if a signup bonus is configured — a signup-bonus commission.
     *
     * Returns null when the user wasn't referred or the referrer isn't enrolled.
     */
    public function execute(User $newUser): ?Referral
    {
        if (! settings('affiliate_enabled', true)) {
            return null;
        }

        $referrer = $newUser->referrer; // belongsTo via referred_by
        if (! $referrer) {
            return null;
        }

        $account = $referrer->affiliateAccount;
        if (! $account || $account->status !== AffiliateStatus::Active) {
            return null;
        }

        // Guard against duplicates (referred_user_id is unique anyway).
        if (Referral::where('referred_user_id', $newUser->id)->exists()) {
            return null;
        }

        return DB::transaction(function () use ($account, $referrer, $newUser) {
            $bonus = (int) settings('affiliate_signup_bonus', 0);

            $referral = Referral::create([
                'affiliate_account_id' => $account->id,
                'referrer_id'          => $referrer->id,
                'referred_user_id'     => $newUser->id,
                'status'               => ReferralStatus::Pending,
                'signup_reward'        => $bonus,
            ]);

            $account->increment('signups_count');

            // Attribute the most recent un-converted click to this signup.
            $account->clicks()
                ->where('converted', false)
                ->latest('created_at')
                ->first()
                ?->update(['converted' => true, 'converted_at' => now()]);

            if ($bonus > 0) {
                $commission = AffiliateCommission::create([
                    'affiliate_account_id' => $account->id,
                    'referral_id'          => $referral->id,
                    'buyer_id'             => $newUser->id,
                    'type'                 => CommissionType::SignupBonus,
                    'amount'               => $bonus,
                    'status'               => CommissionStatus::Pending,
                    'note'                 => "Signup bonus for {$newUser->name}",
                ]);

                $account->increment('total_earned', $bonus);

                if (settings('affiliate_auto_approve', false)) {
                    $this->approveAction->execute($commission);
                }
            }

            return $referral;
        });
    }
}
