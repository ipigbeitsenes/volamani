<?php

namespace App\Actions\Matching;

use App\Enums\MatchRequestStatus;
use App\Models\MatchRequest;
use App\Models\User;
use App\Services\Matching\MatchingEngine;
use Illuminate\Support\Facades\DB;

class CreateMatchRequestAction
{
    public function __construct(private MatchingEngine $engine) {}

    /**
     * Create a matching brief and immediately run the engine to surface vendors.
     */
    public function execute(User $user, array $data): MatchRequest
    {
        return DB::transaction(function () use ($user, $data) {
            $request = $user->matchRequests()->create([
                'looking_for' => $data['looking_for'] ?? 'vendor',
                'title' => $data['title'],
                'description' => $data['description'],
                'category' => $data['category'] ?? null,
                'budget_min' => $data['budget_min'] ?? null,
                'budget_max' => $data['budget_max'] ?? null,
                'preferred_location' => $data['preferred_location'] ?? null,
                'remote_ok' => $data['remote_ok'] ?? true,
                'skills' => $data['skills'] ?? null,
                'timeline' => $data['timeline'] ?? null,
                'status' => MatchRequestStatus::Open,
            ]);

            $this->engine->generate($request);

            return $request->fresh();
        });
    }
}
