<?php

namespace App\Actions\Vendors;

use App\Models\User;
use App\Models\VendorStrike;
use Illuminate\Support\Facades\DB;

class ClearStrikeAction
{
    /**
     * Clear a single active strike (admin action). Decrements the vendor's
     * active-strike counter. Does NOT auto-reactivate a suspended store —
     * lifting a suspension stays a deliberate admin decision.
     */
    public function execute(VendorStrike $strike, User $admin): VendorStrike
    {
        abort_unless($strike->isActive(), 422, 'This strike has already been cleared.');

        return DB::transaction(function () use ($strike, $admin) {
            $strike->update([
                'cleared_at' => now(),
                'cleared_by' => $admin->id,
            ]);

            $vendor = $strike->vendor;

            if ($vendor) {
                $vendor->update([
                    'strikes'            => $vendor->strikes()->active()->count(),
                    'strikes_updated_at' => now(),
                ]);
            }

            return $strike->fresh();
        });
    }
}
