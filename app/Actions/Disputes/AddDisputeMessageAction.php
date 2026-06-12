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
        Dispute       $dispute,
        User          $sender,
        string        $message,
        ?UploadedFile $attachment = null,
        bool          $isStaff = false
    ): DisputeMessage {
        abort_unless($dispute->isOpen(), 422, 'This dispute is closed; no further messages can be added.');

        return DB::transaction(function () use ($dispute, $sender, $message, $attachment, $isStaff) {
            $path = null;
            $name = null;
            if ($attachment) {
                $path = $attachment->store('disputes/' . $dispute->id, 'public');
                $name = $attachment->getClientOriginalName();
            }

            $entry = DisputeMessage::create([
                'dispute_id'      => $dispute->id,
                'sender_id'       => $sender->id,
                'message'         => $message,
                'attachment'      => $path,
                'attachment_name' => $name,
                'is_staff'        => $isStaff,
            ]);

            // Staff reply puts the ball in the parties' court; a party reply flags it for review.
            $dispute->update([
                'status' => $isStaff ? DisputeStatus::AwaitingResponse : DisputeStatus::UnderReview,
            ]);

            return $entry;
        });
    }
}
