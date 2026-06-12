<?php

namespace App\Actions\Matching;

use App\Enums\MatchRequestStatus;
use App\Models\MatchRequest;

class CloseMatchRequestAction
{
    public function execute(MatchRequest $request): MatchRequest
    {
        $request->update(['status' => MatchRequestStatus::Closed]);

        return $request->fresh();
    }
}
