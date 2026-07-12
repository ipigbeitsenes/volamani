<?php

namespace App\Repositories\Social;

use App\Models\Follow;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class FollowRepository
{
    public function isFollowing(User $user, Vendor $vendor): bool
    {
        return Follow::where('follower_id', $user->id)
            ->where('vendor_id', $vendor->id)
            ->exists();
    }

    /** Active vendors this user follows, most-recently-followed first. */
    public function followedVendors(User $user, int $perPage = 12): LengthAwarePaginator
    {
        $vendorIds = Follow::where('follower_id', $user->id)
            ->orderByDesc('created_at')
            ->pluck('vendor_id');

        return Vendor::query()
            ->whereIn('id', $vendorIds)
            ->where('status', 'active')
            ->withCount(['products as active_products_count' => fn ($q) => $q->where('status', 'active')])
            ->orderByRaw('FIELD(id, '.($vendorIds->isEmpty() ? '0' : $vendorIds->implode(',')).')')
            ->paginate($perPage);
    }

    /** Followers of a vendor — the recipients for new-listing announcements. */
    public function followerUsers(Vendor $vendor): Collection
    {
        $userIds = Follow::where('vendor_id', $vendor->id)->pluck('follower_id');

        if ($userIds->isEmpty()) {
            return collect();
        }

        return User::whereIn('id', $userIds)->get();
    }
}
