<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reviews\RespondReviewRequest;
use App\Models\Review;
use App\Services\Reviews\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VendorReviewController extends Controller
{
    public function __construct(private ReviewService $reviewService) {}

    public function index(): View
    {
        $vendor = auth()->user()->vendor;
        $reviews = $this->reviewService->forVendor(auth()->user());

        return view('vendor.reviews.index', compact('vendor', 'reviews'));
    }

    public function respond(RespondReviewRequest $request, Review $review): RedirectResponse
    {
        $this->reviewService->respond($review, auth()->user(), $request->validated()['response']);

        $this->flashSuccess('Your response has been posted.');

        return redirect()->route('vendor.reviews.index');
    }
}
