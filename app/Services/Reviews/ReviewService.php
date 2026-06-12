<?php

namespace App\Services\Reviews;

use App\Actions\Reviews\ModerateReviewAction;
use App\Actions\Reviews\RespondToReviewAction;
use App\Actions\Reviews\SubmitReviewAction;
use App\Actions\Reviews\ToggleHelpfulAction;
use App\Models\ConsultantProfile;
use App\Models\FreelanceService;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Repositories\Reviews\ReviewRepository;
use Illuminate\Database\Eloquent\Model;

class ReviewService
{
    /** Client-safe aliases → reviewable model classes. */
    private const REVIEWABLES = [
        'product'    => Product::class,
        'service'    => FreelanceService::class,
        'consultant' => ConsultantProfile::class,
    ];

    public function __construct(
        private SubmitReviewAction       $submitAction,
        private RespondToReviewAction    $respondAction,
        private ToggleHelpfulAction      $helpfulAction,
        private ModerateReviewAction     $moderateAction,
        private ReviewEligibilityService $eligibility,
        private ReviewRepository         $repo,
    ) {}

    public function resolveReviewable(string $type, int|string $id): Model
    {
        $class = self::REVIEWABLES[$type] ?? abort(404, 'Unknown review target.');

        return $class::findOrFail($id);
    }

    public function submit(User $user, string $type, int|string $id, array $data): Review
    {
        $reviewable = $this->resolveReviewable($type, $id);

        return $this->submitAction->execute($user, $reviewable, $data);
    }

    public function respond(Review $review, User $vendorUser, string $response): Review
    {
        return $this->respondAction->execute($review, $vendorUser, $response);
    }

    public function toggleHelpful(Review $review, User $user): bool
    {
        return $this->helpfulAction->execute($review, $user);
    }

    public function setApproved(Review $review, bool $approved): Review
    {
        return $this->moderateAction->setApproved($review, $approved);
    }

    public function delete(Review $review): void
    {
        $this->moderateAction->delete($review);
    }

    public function canReview(?User $user, Model $reviewable): bool
    {
        return $this->eligibility->canReview($user, $reviewable);
    }

    // ─── Query passthroughs ─────────────────────────────────────────────────────

    public function forVendor(User $vendorUser, int $perPage = 15)
    {
        return $this->repo->forVendorUser($vendorUser, $perPage);
    }

    public function forAdmin(int $perPage = 20, array $filters = [])
    {
        return $this->repo->allForAdmin($perPage, $filters);
    }
}
