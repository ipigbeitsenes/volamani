<?php

namespace App\Actions\Affiliate;

use App\Enums\AffiliateStatus;
use App\Models\AffiliateAccount;
use App\Models\User;

class EnrollAffiliateAction
{
    /**
     * Opt a user into the affiliate program. Idempotent — returns the existing
     * account if they have already joined.
     */
    public function execute(User $user): AffiliateAccount
    {
        if ($user->affiliateAccount) {
            return $user->affiliateAccount;
        }

        return AffiliateAccount::create([
            'user_id' => $user->id,
            'status' => AffiliateStatus::Active,
            'joined_at' => now(),
        ]);
    }
}
