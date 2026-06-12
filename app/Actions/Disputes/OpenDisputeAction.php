<?php

namespace App\Actions\Disputes;

use App\Enums\DisputeStatus;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\Escrow;
use App\Models\User;
use App\Services\Escrow\EscrowService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class OpenDisputeAction
{
    public function __construct(private EscrowService $escrowService) {}

    /**
     * Open a dispute against an escrow and freeze its funds.
     * The raiser must be the buyer or the vendor on the escrow.
     */
    public function execute(
        Escrow        $escrow,
        User          $raisedBy,
        string        $reason,
        string        $description,
        ?UploadedFile $attachment = null
    ): Dispute {
        abort_unless($this->isParty($escrow, $raisedBy), 403, 'You are not a party to this transaction.');

        if ($escrow->isProductEscrow()) {
            // Digital purchases: only the buyer may raise a ticket, and only
            // within the 24h post-purchase window.
            abort_unless(
                $escrow->buyer_id === $raisedBy->id,
                403,
                'Only the buyer can open a support ticket for this purchase.'
            );
            abort_unless(
                $escrow->canRaiseTicket(),
                422,
                'The 24-hour window to open a support ticket for this purchase has closed.'
            );
        } else {
            abort_unless($escrow->canDispute(), 422, 'These funds can no longer be disputed.');
        }

        abort_if(
            Dispute::where('escrow_id', $escrow->id)->whereNull('deleted_at')->exists(),
            422,
            'A dispute already exists for this transaction.'
        );

        return DB::transaction(function () use ($escrow, $raisedBy, $reason, $description, $attachment) {
            $dispute = Dispute::create([
                'escrow_id'   => $escrow->id,
                'buyer_id'    => $escrow->buyer_id,
                'vendor_id'   => $escrow->vendor_id,
                'raised_by'   => $raisedBy->id,
                'reason'      => $reason,
                'description' => $description,
                'status'      => DisputeStatus::Open,
            ]);

            // Freeze the escrow so nothing auto-releases while under review.
            $this->escrowService->dispute($escrow, $raisedBy, "Dispute {$dispute->reference} opened");

            $path = null;
            $name = null;
            if ($attachment) {
                $path = $attachment->store('disputes/' . $dispute->id, 'public');
                $name = $attachment->getClientOriginalName();
            }

            // Opening statement.
            DisputeMessage::create([
                'dispute_id'      => $dispute->id,
                'sender_id'       => $raisedBy->id,
                'message'         => $description,
                'attachment'      => $path,
                'attachment_name' => $name,
            ]);

            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'message'    => 'Dispute opened. Funds are now held until this is resolved.',
                'is_system'  => true,
            ]);

            return $dispute->fresh();
        });
    }

    private function isParty(Escrow $escrow, User $user): bool
    {
        return $escrow->buyer_id === $user->id
            || ($escrow->vendor && $escrow->vendor->user_id === $user->id);
    }
}
