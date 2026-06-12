<?php

namespace App\Services\Matching;

use App\Actions\Matching\CloseMatchRequestAction;
use App\Actions\Matching\CreateMatchRequestAction;
use App\Actions\Matching\RespondToMatchAction;
use App\Actions\Matching\UpsertMatchingProfileAction;
use App\Enums\MatchStatus;
use App\Models\BusinessMatch;
use App\Models\MatchingProfile;
use App\Models\MatchRequest;
use App\Models\User;
use App\Models\Vendor;
use App\Repositories\Matching\MatchingRepository;

class MatchingService
{
    public function __construct(
        private CreateMatchRequestAction    $createAction,
        private RespondToMatchAction        $respondAction,
        private UpsertMatchingProfileAction $profileAction,
        private CloseMatchRequestAction     $closeAction,
        private MatchingEngine              $engine,
        private MatchingRepository          $repo,
    ) {}

    public function createRequest(User $user, array $data): MatchRequest
    {
        return $this->createAction->execute($user, $data);
    }

    public function regenerate(MatchRequest $request): int
    {
        return $this->engine->generate($request);
    }

    public function respondAsRequester(BusinessMatch $match, bool $interested): BusinessMatch
    {
        return $this->respondAction->asRequester($match, $interested);
    }

    public function respondAsVendor(BusinessMatch $match, bool $interested): BusinessMatch
    {
        return $this->respondAction->asVendor($match, $interested);
    }

    public function saveProfile(Vendor $vendor, array $data): MatchingProfile
    {
        return $this->profileAction->execute($vendor, $data);
    }

    public function close(MatchRequest $request): MatchRequest
    {
        return $this->closeAction->execute($request);
    }

    /** Mark a suggested match as viewed by the requester. */
    public function markViewed(BusinessMatch $match): void
    {
        if ($match->status === MatchStatus::Suggested) {
            $match->update(['status' => MatchStatus::Viewed, 'viewed_at' => now()]);
        }
    }

    // ─── Query passthroughs ──────────────────────────────────────────────────────

    public function requestsForUser(User $user, int $perPage = 10)
    {
        return $this->repo->requestsForUser($user, $perPage);
    }

    public function leadsForVendor(Vendor $vendor, int $perPage = 12, array $filters = [])
    {
        return $this->repo->leadsForVendor($vendor, $perPage, $filters);
    }

    public function profileForVendor(Vendor $vendor): ?MatchingProfile
    {
        return $this->repo->profileForVendor($vendor);
    }

    public function vendorStats(Vendor $vendor): array
    {
        return $this->repo->vendorStats($vendor);
    }
}
