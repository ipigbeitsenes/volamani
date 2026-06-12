<?php

namespace App\Actions\Disputes;

use App\Enums\DisputeStatus;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EscalateDisputeAction
{
    /**
     * Escalate a dispute for senior/manual review. Funds stay frozen.
     */
    public function execute(Dispute $dispute, User $admin, ?string $note = null): Dispute
    {
        abort_unless($dispute->canBeEscalated(), 422, 'This dispute cannot be escalated at this stage.');

        return DB::transaction(function () use ($dispute, $admin, $note) {
            $dispute->update([
                'status'       => DisputeStatus::Escalated,
                'escalated_at' => now(),
            ]);

            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'sender_id'  => $admin->id,
                'message'    => 'Dispute escalated for senior review.' . ($note ? " {$note}" : ''),
                'is_staff'   => true,
                'is_system'  => true,
            ]);

            return $dispute->fresh();
        });
    }
}
