<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\Reviews\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReviewModerationController extends Controller
{
    public function __construct(private ReviewService $reviewService) {}

    public function index(): View
    {
        $filters = request()->only(['approved', 'rating', 'search']);
        $reviews = $this->reviewService->forAdmin(20, $filters);

        return view('admin.reviews.index', compact('reviews', 'filters'));
    }

    public function approve(Review $review): RedirectResponse
    {
        $this->reviewService->setApproved($review, true);
        $this->flashSuccess('Review approved and visible.');

        return back();
    }

    public function hide(Review $review): RedirectResponse
    {
        $this->reviewService->setApproved($review, false);
        $this->flashWarning('Review hidden from public view.');

        return back();
    }

    public function destroy(Review $review): RedirectResponse
    {
        $this->reviewService->delete($review);
        $this->flashSuccess('Review deleted.');

        return back();
    }
}
