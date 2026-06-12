<?php

namespace App\Actions\Reviews;

use App\Models\Review;
use App\Services\Reviews\RatingAggregationService;
use Illuminate\Support\Facades\DB;

class ModerateReviewAction
{
    public function __construct(private RatingAggregationService $aggregator) {}

    /**
     * Approve or hide a review. Hidden reviews drop out of aggregates immediately.
     */
    public function setApproved(Review $review, bool $approved): Review
    {
        return DB::transaction(function () use ($review, $approved) {
            $review->update(['is_approved' => $approved]);

            if ($review->reviewable) {
                $this->aggregator->sync($review->reviewable);
            }

            return $review->fresh();
        });
    }

    public function delete(Review $review): void
    {
        DB::transaction(function () use ($review) {
            $reviewable = $review->reviewable;
            $review->delete();

            if ($reviewable) {
                $this->aggregator->sync($reviewable);
            }
        });
    }
}
