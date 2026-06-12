<?php

namespace App\Http\Controllers\Matching;

use App\Http\Controllers\Controller;
use App\Http\Requests\Matching\MatchRequestRequest;
use App\Models\BusinessMatch;
use App\Models\MatchRequest;
use App\Services\Matching\MatchingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MatchRequestController extends Controller
{
    public function __construct(private MatchingService $matchingService) {}

    public function index(): View
    {
        $requests = $this->matchingService->requestsForUser(auth()->user());

        return view('marketplace.matching.index', compact('requests'));
    }

    public function create(): View
    {
        return view('marketplace.matching.create');
    }

    public function store(MatchRequestRequest $request): RedirectResponse
    {
        $matchRequest = $this->matchingService->createRequest(auth()->user(), $request->requestData());

        $count = $matchRequest->matches_count;
        $this->flashSuccess($count > 0
            ? "We found {$count} matching vendor(s) for your brief."
            : 'Your brief is live. We\'ll surface vendors as they join.');

        return redirect()->route('matching.show', $matchRequest);
    }

    public function show(MatchRequest $matchRequest): View
    {
        $this->authorizeOwner($matchRequest);

        $matchRequest->load(['matches.vendor.matchingProfile', 'matches.vendor.user']);

        // Opening the brief marks freshly suggested matches as viewed.
        $matchRequest->matches->each(fn (BusinessMatch $m) => $this->matchingService->markViewed($m));

        return view('marketplace.matching.show', ['request' => $matchRequest->fresh(['matches.vendor.matchingProfile', 'matches.vendor.user'])]);
    }

    public function close(MatchRequest $matchRequest): RedirectResponse
    {
        $this->authorizeOwner($matchRequest);

        $this->matchingService->close($matchRequest);
        $this->flashSuccess('Brief closed.');

        return redirect()->route('matching.index');
    }

    public function respond(MatchRequest $matchRequest, BusinessMatch $match): RedirectResponse
    {
        $this->authorizeOwner($matchRequest);
        abort_unless($match->match_request_id === $matchRequest->id, 404);

        $interested = request()->input('decision') === 'interested';
        $this->matchingService->respondAsRequester($match, $interested);

        $this->flashSuccess($interested
            ? 'Interest sent. If the vendor is also interested, you\'ll be connected.'
            : 'Match dismissed.');

        return back();
    }

    private function authorizeOwner(MatchRequest $request): void
    {
        abort_unless($request->user_id === auth()->id(), 403);
    }
}
