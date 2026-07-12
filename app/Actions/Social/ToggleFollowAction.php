<?php

namespace App\Actions\Social;

use App\Models\Follow;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class ToggleFollowAction
{
    /**
     * Follow or unfollow a vendor. Returns the new follow state
     * (true = now following, false = now unfollowed). Keeps the
     * cached vendors.followers_count column in sync.
     */
    public function execute(User $user, Vendor $vendor): bool
    {
        return DB::transaction(function () use ($user, $vendor) {
            $existing = Follow::where('follower_id', $user->id)
                ->where('vendor_id', $vendor->id)
                ->first();

            if ($existing) {
                $existing->delete();
                if ($vendor->followers_count > 0) {
                    $vendor->decrement('followers_count');
                }

                return false;
            }

            Follow::create([
                'follower_id' => $user->id,
                'vendor_id' => $vendor->id,
            ]);
            $vendor->increment('followers_count');

            return true;
        });
    }
}
