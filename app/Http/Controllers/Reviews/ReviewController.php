<?php

namespace App\Http\Controllers\Reviews;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reviews\SubmitReviewRequest;
use App\Models\Review;
use App\Services\Reviews\ReviewService;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function __construct(private ReviewService $reviewService) {}

    public function store(SubmitReviewRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->reviewService->submit(
            auth()->user(),
            $data['reviewable_type'],
            $data['reviewable_id'],
            $data
        );

        $this->flashSuccess('Thanks! Your review has been posted.');

        return back();
    }

    public function helpful(Review $review): RedirectResponse
    {
        $marked = $this->reviewService->toggleHelpful($review, auth()->user());

        $this->flashInfo($marked ? 'Marked as helpful.' : 'Removed your helpful vote.');

        return back();
    }
}
