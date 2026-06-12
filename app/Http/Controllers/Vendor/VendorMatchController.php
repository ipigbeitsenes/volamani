<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Matching\MatchingProfileRequest;
use App\Models\BusinessMatch;
use App\Services\Matching\MatchingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorMatchController extends Controller
{
    public function __construct(private MatchingService $matchingService) {}

    public function index(Request $request): View
    {
        $vendor  = $request->user()->vendor;
        $filters = $request->only('status');
        $leads   = $this->matchingService->leadsForVendor($vendor, 12, $filters);
        $profile = $this->matchingService->profileForVendor($vendor);
        $stats   = $this->matchingService->vendorStats($vendor);

        return view('vendor.matching.index', compact('leads', 'profile', 'stats', 'filters'));
    }

    public function profile(): View
    {
        $vendor  = auth()->user()->vendor;
        $profile = $this->matchingService->profileForVendor($vendor);

        return view('vendor.matching.profile', compact('profile'));
    }

    public function saveProfile(MatchingProfileRequest $request): RedirectResponse
    {
        $this->matchingService->saveProfile($request->user()->vendor, $request->profileData());

        $this->flashSuccess('Matching profile saved. You\'ll start receiving relevant leads.');

        return redirect()->route('vendor.matching.index');
    }

    public function respond(Request $request, BusinessMatch $match): RedirectResponse
    {
        abort_unless($match->vendor_id === auth()->user()->vendor?->id, 403);

        $interested = $request->input('decision') === 'interested';
        $this->matchingService->respondAsVendor($match, $interested);

        $this->flashSuccess($interested
            ? 'Interest registered. We\'ll connect you if the client is interested too.'
            : 'Lead dismissed.');

        return back();
    }
}
