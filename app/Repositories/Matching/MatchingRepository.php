<?php

namespace App\Repositories\Matching;

use App\Enums\MatchStatus;
use App\Models\BusinessMatch;
use App\Models\MatchingProfile;
use App\Models\MatchRequest;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MatchingRepository
{
    public function requestsForUser(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return MatchRequest::where('user_id', $user->id)
            ->withCount('matches')
            ->latest()
            ->paginate($perPage);
    }

    public function leadsForVendor(Vendor $vendor, int $perPage = 12, array $filters = []): LengthAwarePaginator
    {
        $query = BusinessMatch::where('vendor_id', $vendor->id)
            ->with(['matchRequest.user'])
            ->orderByDesc('score');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            // Hide leads the vendor already passed on by default.
            $query->where('status', '!=', MatchStatus::Declined);
        }

        return $query->paginate($perPage);
    }

    public function profileForVendor(Vendor $vendor): ?MatchingProfile
    {
        return MatchingProfile::where('vendor_id', $vendor->id)->first();
    }

    public function vendorStats(Vendor $vendor): array
    {
        $base = BusinessMatch::where('vendor_id', $vendor->id);

        return [
            'leads' => (clone $base)->count(),
            'pending' => (clone $base)->whereIn('status', [MatchStatus::Suggested, MatchStatus::Viewed, MatchStatus::Interested])->count(),
            'connected' => (clone $base)->where('status', MatchStatus::Connected)->count(),
        ];
    }
}
