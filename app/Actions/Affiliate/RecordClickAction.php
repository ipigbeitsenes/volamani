<?php

namespace App\Actions\Affiliate;

use App\Models\AffiliateAccount;
use App\Models\AffiliateClick;
use Illuminate\Http\Request;

class RecordClickAction
{
    /**
     * Log a visit to an affiliate share link and bump the account's click counter.
     */
    public function execute(AffiliateAccount $account, Request $request): AffiliateClick
    {
        $click = AffiliateClick::create([
            'affiliate_account_id' => $account->id,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'landing_page' => $request->fullUrl(),
            'referrer_url' => $request->headers->get('referer'),
            'converted' => false,
            'created_at' => now(),
        ]);

        $account->increment('clicks_count');

        return $click;
    }
}
