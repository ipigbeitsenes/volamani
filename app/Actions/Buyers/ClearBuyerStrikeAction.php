<?php

namespace App\Actions\Buyers;

use App\Models\BuyerStrike;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Clear a single active buyer strike (admin action) and recompute standing.
 * Unlike vendor suspensions, a buyer's flag/suspension is derived directly from
 * the remaining active-strike count, so clearing enough strikes automatically
 * lifts the restriction.
 */
class ClearBuyerStrikeAction
{
    public function execute(BuyerStrike $strike, User $admin): BuyerStrike
    {
        abort_unless($strike->isActive(), 422, 'This strike has already been cleared.');

        return DB::transaction(function () use ($strike, $admin) {
            $strike->update([
                'cleared_at' => now(),
                'cleared_by' => $admin->id,
            ]);

            if ($buyer = $strike->user) {
                $active    = $buyer->buyerStrikes()->active()->count();
                $flagged   = $active >= $this->flagThreshold();
                $suspended = $active >= $this->suspendThreshold();

                $buyer->update([
                    'buyer_strikes'            => $active,
                    'buyer_strikes_updated_at' => now(),
                    'buyer_flagged'            => $flagged,
                    'buyer_flagged_at'         => $flagged ? ($buyer->buyer_flagged_at ?? now()) : null,
                    'purchases_suspended'      => $suspended,
                    'purchases_suspended_at'   => $suspended ? ($buyer->purchases_suspended_at ?? now()) : null,
                ]);
            }

            return $strike->fresh();
        });
    }

    private function flagThreshold(): int
    {
        $v = settings('buyer_flag_threshold');

        return (int) (($v === null || $v === '') ? config('protection.buyer_flag_threshold', 2) : $v);
    }

    private function suspendThreshold(): int
    {
        $v = settings('buyer_suspend_threshold');

        return (int) (($v === null || $v === '') ? config('protection.buyer_suspend_threshold', 4) : $v);
    }
}
