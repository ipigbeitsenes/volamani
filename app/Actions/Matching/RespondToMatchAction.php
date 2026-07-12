<?php

namespace App\Actions\Matching;

use App\Enums\MatchRequestStatus;
use App\Enums\MatchStatus;
use App\Models\BusinessMatch;
use Illuminate\Support\Facades\DB;

class RespondToMatchAction
{
    /** The buyer who posted the brief reacts to a suggested vendor. */
    public function asRequester(BusinessMatch $match, bool $interested): BusinessMatch
    {
        return $this->apply($match, requesterInterested: $interested);
    }

    /** A suggested vendor reacts to an incoming lead. */
    public function asVendor(BusinessMatch $match, bool $interested): BusinessMatch
    {
        return $this->apply($match, vendorInterested: $interested);
    }

    private function apply(BusinessMatch $match, ?bool $requesterInterested = null, ?bool $vendorInterested = null): BusinessMatch
    {
        return DB::transaction(function () use ($match, $requesterInterested, $vendorInterested) {
            if ($requesterInterested !== null) {
                $match->requester_interested = $requesterInterested;
            }
            if ($vendorInterested !== null) {
                $match->vendor_interested = $vendorInterested;
            }

            // A decline from either side ends the match.
            if ($requesterInterested === false || $vendorInterested === false) {
                $match->status = MatchStatus::Declined;
                $match->save();

                return $match->fresh();
            }

            if ($match->requester_interested && $match->vendor_interested) {
                $match->status = MatchStatus::Connected;
                $match->connected_at = $match->connected_at ?? now();
                $match->save();

                $match->vendor->matchingProfile?->increment('connections_count');

                // First connection flips the brief to "matched".
                if ($match->matchRequest->status === MatchRequestStatus::Open) {
                    $match->matchRequest->update(['status' => MatchRequestStatus::Matched]);
                }
            } elseif ($match->status !== MatchStatus::Connected) {
                $match->status = MatchStatus::Interested;
                $match->save();
            }

            return $match->fresh();
        });
    }
}
