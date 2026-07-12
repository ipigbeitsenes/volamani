<?php

namespace App\Services\Reviews;

use App\Enums\ConsultationSessionStatus;
use App\Enums\ServiceOrderStatus;
use App\Models\ConsultantProfile;
use App\Models\ConsultationSession;
use App\Models\FreelanceService;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ReviewEligibilityService
{
    /** Has the user already reviewed this item? */
    public function hasReviewed(User $user, Model $reviewable): bool
    {
        return Review::where('reviewable_type', get_class($reviewable))
            ->where('reviewable_id', $reviewable->getKey())
            ->where('reviewer_id', $user->id)
            ->exists();
    }

    /** Did the user complete a qualifying purchase of this item? */
    public function hasPurchased(User $user, Model $reviewable): bool
    {
        return match (true) {
            $reviewable instanceof Product => $reviewable->hasPurchased($user),

            $reviewable instanceof FreelanceService => ServiceOrder::where('buyer_id', $user->id)
                ->where('service_id', $reviewable->id)
                ->where('status', ServiceOrderStatus::Completed)
                ->exists(),

            $reviewable instanceof ConsultantProfile => ConsultationSession::where('buyer_id', $user->id)
                ->where('profile_id', $reviewable->id)
                ->where('status', ConsultationSessionStatus::Completed)
                ->exists(),

            default => false,
        };
    }

    public function canReview(?User $user, Model $reviewable): bool
    {
        return $user
            && $this->hasPurchased($user, $reviewable)
            && ! $this->hasReviewed($user, $reviewable);
    }

    /** The order linkage columns to store on the review, if resolvable. */
    public function linkage(User $user, Model $reviewable): array
    {
        if ($reviewable instanceof FreelanceService) {
            $orderId = ServiceOrder::where('buyer_id', $user->id)
                ->where('service_id', $reviewable->id)
                ->where('status', ServiceOrderStatus::Completed)
                ->value('id');

            return ['service_order_id' => $orderId];
        }

        if ($reviewable instanceof Product) {
            $orderId = OrderItem::whereHas('order', fn ($q) => $q->where('buyer_id', $user->id)->where('payment_status', 'success'))
                ->where('product_id', $reviewable->id)
                ->value('order_id');

            return ['order_id' => $orderId];
        }

        return [];
    }
}
