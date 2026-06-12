<?php

namespace App\Services\Reviews;

use App\Enums\KYCStatus;
use App\Models\Vendor;

class TrustScoreService
{
    /**
     * Compute a 0–100 trust score from a vendor's quality signals.
     *
     *   Rating quality   up to 50  (average_rating / 5)
     *   Review volume    up to 20  (2 pts per review, capped)
     *   KYC verified         15
     *   Vendor verified      10
     *   Account tenure        5  (90+ days)
     */
    public function calculate(Vendor $vendor): int
    {
        $score = 0.0;

        $score += ($vendor->average_rating / 5) * 50;
        $score += min(20, $vendor->reviews_count * 2);

        if ($vendor->user && $vendor->user->kyc_status === KYCStatus::Verified) {
            $score += 15;
        }

        if ($vendor->isVerified()) {
            $score += 10;
        }

        if ($vendor->created_at && $vendor->created_at->lt(now()->subDays(90))) {
            $score += 5;
        }

        return (int) min(100, round($score));
    }

    public function sync(Vendor $vendor): void
    {
        $vendor->update(['trust_score' => $this->calculate($vendor)]);
    }
}
