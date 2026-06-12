<?php

namespace App\Services\Matching;

use App\Enums\MatchStatus;
use App\Enums\Status;
use App\Models\BusinessMatch;
use App\Models\MatchingProfile;
use App\Models\MatchRequest;
use App\Models\Vendor;

/**
 * Scores vendors against a buyer's brief and materialises the best matches.
 * Weights (max 100): category 30, skills 25, trust 15, budget 15, location 10,
 * verified 5.
 */
class MatchingEngine
{
    public function generate(MatchRequest $request): int
    {
        $minScore   = (int) settings('match_min_score', 40);
        $maxResults = (int) settings('match_max_results', 20);

        $candidates = Vendor::query()
            ->where('status', Status::Active)
            ->whereHas('matchingProfile', fn ($q) => $q->where('is_accepting', true))
            ->with('matchingProfile')
            ->when($request->user->vendor, fn ($q, $v) => $q->where('id', '!=', $v->id))
            ->get();

        $scored = $candidates
            ->map(function (Vendor $vendor) use ($request) {
                $result = $this->score($request, $vendor, $vendor->matchingProfile);

                return ['vendor' => $vendor, 'score' => $result['score'], 'breakdown' => $result['breakdown']];
            })
            ->filter(fn ($row) => $row['score'] >= $minScore)
            ->sortByDesc('score')
            ->take($maxResults);

        $count = 0;
        foreach ($scored as $row) {
            $match = BusinessMatch::firstOrNew([
                'match_request_id' => $request->id,
                'vendor_id'        => $row['vendor']->id,
            ]);

            $isNew = ! $match->exists;

            $match->score           = $row['score'];
            $match->score_breakdown = $row['breakdown'];
            if ($isNew) {
                $match->status = MatchStatus::Suggested;
            }
            $match->save();

            if ($isNew) {
                $row['vendor']->matchingProfile?->increment('leads_count');
                $count++;
            }
        }

        $request->update(['matches_count' => $request->matches()->count()]);

        return $count;
    }

    /**
     * @return array{score:int, breakdown:array<string,int>}
     */
    public function score(MatchRequest $request, Vendor $vendor, MatchingProfile $profile): array
    {
        $breakdown = [
            'category' => $this->categoryScore($request, $vendor, $profile),
            'skills'   => $this->skillsScore($request, $profile),
            'budget'   => $this->budgetScore($request, $profile),
            'location' => $this->locationScore($request, $vendor, $profile),
            'trust'    => (int) round(((int) $vendor->trust_score) / 100 * 15),
            'verified' => $vendor->isVerified() ? 5 : 0,
        ];

        return ['score' => min(100, array_sum($breakdown)), 'breakdown' => $breakdown];
    }

    private function categoryScore(MatchRequest $request, Vendor $vendor, MatchingProfile $profile): int
    {
        if (! $request->category) {
            return 15; // no preference — neutral
        }

        $wanted = strtolower($request->category);
        $pool   = array_map('strtolower', $profile->categories ?? []);
        if ($vendor->category) {
            $pool[] = strtolower($vendor->category);
        }

        return in_array($wanted, $pool, true) ? 30 : 0;
    }

    private function skillsScore(MatchRequest $request, MatchingProfile $profile): int
    {
        $wanted = $this->normalize($request->skills ?? []);
        if (empty($wanted)) {
            return 12; // neutral
        }

        $have    = $this->normalize($profile->skills ?? []);
        $overlap = count(array_intersect($wanted, $have));

        return (int) round($overlap / count($wanted) * 25);
    }

    private function budgetScore(MatchRequest $request, MatchingProfile $profile): int
    {
        $briefMin = $request->budget_min;
        $briefMax = $request->budget_max;

        if (($briefMin === null && $briefMax === null) || ($profile->min_budget === null && $profile->max_budget === null)) {
            return 8; // unknown on either side — neutral
        }

        $bMin = $briefMin ?? 0;
        $bMax = $briefMax ?? PHP_INT_MAX;
        $pMin = $profile->min_budget ?? 0;
        $pMax = $profile->max_budget ?? PHP_INT_MAX;

        return ($bMax >= $pMin && $bMin <= $pMax) ? 15 : 0;
    }

    private function locationScore(MatchRequest $request, Vendor $vendor, MatchingProfile $profile): int
    {
        if ($request->remote_ok && $profile->serves_remote) {
            return 10;
        }

        if (! $request->preferred_location) {
            return 5; // no preference
        }

        $loc  = strtolower($request->preferred_location);
        $pool = array_map('strtolower', array_filter([$vendor->city, $vendor->state, ...($profile->locations ?? [])]));

        foreach ($pool as $candidate) {
            if (str_contains($loc, $candidate) || str_contains($candidate, $loc)) {
                return 10;
            }
        }

        return 0;
    }

    /** @return list<string> lowercase, trimmed, de-duped */
    private function normalize(array $values): array
    {
        return collect($values)
            ->map(fn ($v) => strtolower(trim((string) $v)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
