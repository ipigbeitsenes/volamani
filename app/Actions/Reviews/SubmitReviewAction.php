<?php

namespace App\Actions\Reviews;

use App\Enums\NotificationCategory;
use App\Models\Review;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use App\Services\Reviews\RatingAggregationService;
use App\Services\Reviews\ReviewEligibilityService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SubmitReviewAction
{
    public function __construct(
        private ReviewEligibilityService  $eligibility,
        private RatingAggregationService  $aggregator,
        private NotificationService       $notifications,
    ) {}

    public function execute(User $user, Model $reviewable, array $data): Review
    {
        abort_unless($this->eligibility->hasPurchased($user, $reviewable), 403,
            'You can only review items you have purchased and received.');
        abort_if($this->eligibility->hasReviewed($user, $reviewable), 422,
            'You have already reviewed this item.');

        $review = DB::transaction(function () use ($user, $reviewable, $data) {
            $review = Review::create(array_merge([
                'reviewable_type'      => get_class($reviewable),
                'reviewable_id'        => $reviewable->getKey(),
                'reviewer_id'          => $user->id,
                'rating'               => $data['rating'],
                'title'                => $data['title'] ?? null,
                'body'                 => $data['body'] ?? null,
                'is_approved'          => true,
                'is_verified_purchase' => true,
            ], $this->eligibility->linkage($user, $reviewable)));

            $this->aggregator->sync($reviewable);

            return $review;
        });

        $vendorUser = $reviewable->vendor?->user;
        if ($vendorUser) {
            $this->notifications->send(
                $vendorUser,
                NotificationCategory::Reviews,
                'New review received',
                $user->name . ' left a ' . $review->rating . '-star review on one of your listings.',
                route('vendor.reviews.index'),
                'View reviews',
            );
        }

        return $review;
    }
}
