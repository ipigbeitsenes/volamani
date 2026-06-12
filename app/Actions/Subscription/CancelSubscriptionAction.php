<?php

namespace App\Actions\Subscription;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;

class CancelSubscriptionAction
{
    /**
     * Cancel a subscription. Access is retained until the end of the paid period
     * (no refund) — only auto-renewal is switched off.
     */
    public function execute(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status'       => SubscriptionStatus::Cancelled,
            'auto_renew'   => false,
            'cancelled_at' => now(),
        ]);

        return $subscription->fresh();
    }
}
