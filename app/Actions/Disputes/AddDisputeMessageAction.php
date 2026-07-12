<?php

namespace App\Actions\Disputes;

use App\Enums\DisputeStatus;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class AddDisputeMessageAction
{
    public function execute(
        Dispute $dispute,
        User $sender,
        string $message,
        ?UploadedFile $attachment = null,
        bool $isStaff = false
    ): DisputeMessage {
        abort_unless($dispute->isOpen(), 422, 'This dispute is closed; no further messages can be added.');

        return DB::transaction(function () use ($dispute, $sender, $message, $attachment, $isStaff) {
            $path = null;
            $name = null;
            if ($attachment) {
                $path = $attachment->store('disputes/'.$dispute->id, 'public');
                $name = $attachment->getClientOriginalName();
            }

            $entry = DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'sender_id' => $sender->id,
                'message' => $message,
                'attachment' => $path,
                'attachment_name' => $name,
                'is_staff' => $isStaff,
            ]);

            // Staff reply puts the ball in the parties' court (party response window);
            // a party reply flags it for staff review (admin SLA window). Either way
            // the SLA clock restarts, so a prior breach is cleared for the new cycle.
            $dispute->update([
                'status' => $isStaff ? DisputeStatus::AwaitingResponse : DisputeStatus::UnderReview,
                'response_due_at' => now()->addHours($isStaff ? $this->responseHours() : $this->adminHours()),
                'sla_breached' => false,
            ]);

            return $entry;
        });
    }

    private function responseHours(): int
    {
        $v = settings('dispute_response_hours');

        return (int) (($v === null || $v === '') ? config('protection.dispute_response_hours', 48) : $v);
    }

    private function adminHours(): int
    {
        $v = settings('dispute_admin_sla_hours');

        return (int) (($v === null || $v === '') ? config('protection.dispute_admin_sla_hours', 72) : $v);
    }
}
