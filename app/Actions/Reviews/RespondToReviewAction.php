<?php

namespace App\Actions\Reviews;

use App\Models\Review;
use App\Models\User;

class RespondToReviewAction
{
    /**
     * Vendor posts (or updates) a public reply to a review on one of their items.
     */
    public function execute(Review $review, User $vendorUser, string $response): Review
    {
        abort_unless($this->ownsReviewable($review, $vendorUser), 403,
            'You can only respond to reviews on your own listings.');

        $review->update([
            'response'     => $response,
            'responded_at' => now(),
        ]);

        return $review->fresh();
    }

    private function ownsReviewable(Review $review, User $user): bool
    {
        $reviewable = $review->reviewable;
        $vendor     = $reviewable?->vendor ?? null;

        return $vendor && $vendor->user_id === $user->id;
    }
}
