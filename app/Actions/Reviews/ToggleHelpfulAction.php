<?php

namespace App\Actions\Reviews;

use App\Models\Review;
use App\Models\ReviewVote;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ToggleHelpfulAction
{
    /**
     * Toggle a user's "helpful" vote on a review. Returns the new vote state.
     */
    public function execute(Review $review, User $user): bool
    {
        return DB::transaction(function () use ($review, $user) {
            $vote = ReviewVote::where('review_id', $review->id)
                ->where('user_id', $user->id)
                ->first();

            if ($vote) {
                $vote->delete();
                $review->decrement('helpful_count');
                return false;
            }

            ReviewVote::create(['review_id' => $review->id, 'user_id' => $user->id]);
            $review->increment('helpful_count');
            return true;
        });
    }
}
