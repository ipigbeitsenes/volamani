<?php

namespace App\Services\Reviews;

use App\Models\ConsultantProfile;
use App\Models\FreelanceService;
use App\Models\Product;
use App\Models\Review;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Model;

class RatingAggregationService
{
    public function __construct(private TrustScoreService $trustService) {}

    /**
     * Recalculate cached rating columns for a reviewable and its owning vendor.
     */
    public function sync(Model $reviewable): void
    {
        $this->syncReviewable($reviewable);

        $vendor = $this->vendorFor($reviewable);
        if ($vendor) {
            $this->syncVendor($vendor);
        }
    }

    public function syncReviewable(Model $reviewable): void
    {
        $stats = Review::where('reviewable_type', get_class($reviewable))
            ->where('reviewable_id', $reviewable->getKey())
            ->where('is_approved', true)
            ->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_rating')
            ->first();

        $reviewable->forceFill([
            'reviews_count'  => (int) $stats->cnt,
            'average_rating' => round((float) $stats->avg_rating, 2),
        ])->save();
    }

    public function syncVendor(Vendor $vendor): void
    {
        $stats = $vendor->reviews()
            ->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_rating')
            ->first();

        $vendor->update([
            'reviews_count'  => (int) $stats->cnt,
            'average_rating' => round((float) $stats->avg_rating, 2),
        ]);

        $this->trustService->sync($vendor->fresh());
    }

    private function vendorFor(Model $reviewable): ?Vendor
    {
        return match (true) {
            $reviewable instanceof Product           => $reviewable->vendor,
            $reviewable instanceof FreelanceService  => $reviewable->vendor,
            $reviewable instanceof ConsultantProfile => $reviewable->vendor,
            default                                  => null,
        };
    }
}
