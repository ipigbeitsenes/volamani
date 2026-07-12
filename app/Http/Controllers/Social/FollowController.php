<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Services\Social\FollowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FollowController extends Controller
{
    public function __construct(private FollowService $followService) {}

    public function toggle(Vendor $vendor): RedirectResponse
    {
        $user = auth()->user();

        if ($vendor->user_id === $user->id) {
            $this->flashWarning("You can't follow your own store.");

            return back();
        }

        $nowFollowing = $this->followService->toggle($user, $vendor);

        $this->flashSuccess($nowFollowing
            ? "You're now following {$vendor->business_name}. We'll let you know about new listings."
            : "You unfollowed {$vendor->business_name}.");

        return back();
    }

    public function index(): View
    {
        $vendors = $this->followService->followedVendors(auth()->user());

        return view('marketplace.following.index', compact('vendors'));
    }
}
