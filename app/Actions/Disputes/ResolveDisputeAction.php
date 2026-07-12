<?php

namespace App\Actions\Disputes;

use App\Actions\Buyers\AddBuyerStrikeAction;
use App\Actions\Vendors\AddStrikeAction;
use App\Enums\BuyerStrikeReason;
use App\Enums\DisputeResolution;
use App\Enums\DisputeStatus;
use App\Enums\StrikeReason;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use App\Models\User;
use App\Services\Escrow\EscrowService;
use Illuminate\Support\Facades\DB;

class ResolveDisputeAction
{
    public function __construct(
        private EscrowService $escrowService,
        private AddStrikeAction $addStrike,
        private AddBuyerStrikeAction $addBuyerStrike,
    ) {}

    /**
     * Resolve a dispute (admin only) and settle the underlying escrow accordingly.
     * For a split, $vendorShareKobo is the portion of vendor earnings released;
     * the remainder is refunded to the buyer.
     */
    public function execute(
        Dispute $dispute,
        User $admin,
        DisputeResolution $resolution,
        ?int $vendorShareKobo = null,
        ?string $note = null
    ): Dispute {
        abort_unless($dispute->canBeResolved(), 422, 'This dispute has already been resolved.');

        return DB::transaction(function () use ($dispute, $admin, $resolution, $vendorShareKobo, $note) {
            $escrow = $dispute->escrow;

            $settledAmount = match ($resolution) {
                DisputeResolution::ReleaseToVendor,
                DisputeResolution::Dismissed => $this->releaseAll($escrow, $admin),

                DisputeResolution::RefundToBuyer => $this->refundAll($escrow, $admin, $note),

                DisputeResolution::Split => $this->split($escrow, $admin, (int) $vendorShareKobo, $note),
            };

            $dispute->update([
                'status' => DisputeStatus::Resolved,
                'resolution' => $resolution,
                'resolution_amount' => $settledAmount,
                'resolution_note' => $note,
                'resolved_by' => $admin->id,
                'resolved_at' => now(),
            ]);

            DisputeMessage::create([
                'dispute_id' => $dispute->id,
                'sender_id' => $admin->id,
                'message' => "Dispute resolved: {$resolution->label()}.".($note ? " {$note}" : ''),
                'is_staff' => true,
                'is_system' => true,
            ]);

            // A full refund to the buyer means the seller lost — record a strike.
            if ($resolution === DisputeResolution::RefundToBuyer && $dispute->vendor) {
                $this->addStrike->execute(
                    $dispute->vendor,
                    StrikeReason::LostDispute,
                    "Dispute {$dispute->reference} resolved in the buyer's favour",
                    $dispute->id,
                    $admin,
                );
            }

            // A full release to the seller (or a dismissal) means the buyer's
            // complaint was not upheld — record a buyer abuse strike. Partial
            // "split" outcomes are ambiguous, so they never strike the buyer.
            if (in_array($resolution, [DisputeResolution::ReleaseToVendor, DisputeResolution::Dismissed], true)
                && $dispute->buyer) {
                $this->addBuyerStrike->execute(
                    $dispute->buyer,
                    BuyerStrikeReason::LostDispute,
                    "Dispute {$dispute->reference} resolved in the seller's favour",
                    $dispute->id,
                    $admin,
                );
            }

            return $dispute->fresh();
        });
    }

    private function releaseAll($escrow, User $admin): int
    {
        if ($escrow && $escrow->canRelease()) {
            $amount = $escrow->releasableAmount();
            $this->escrowService->release($escrow, null, $admin);

            return $amount;
        }

        return 0;
    }

    private function refundAll($escrow, User $admin, ?string $note): int
    {
        if ($escrow && $escrow->canRefund()) {
            $amount = $escrow->refundableAmount();
            $this->escrowService->refund($escrow, $admin, $note);

            return $amount;
        }

        return 0;
    }

    private function split($escrow, User $admin, int $vendorShareKobo, ?string $note): int
    {
        if (! $escrow) {
            return 0;
        }

        $share = max(0, min($vendorShareKobo, $escrow->releasableAmount()));

        // Release the vendor's share first…
        if ($share > 0 && $escrow->canRelease()) {
            $this->escrowService->release($escrow, $share, $admin);
        }

        // …then refund whatever remains to the buyer.
        $fresh = $escrow->fresh();
        if ($fresh->canRefund()) {
            $this->escrowService->refund($fresh, $admin, $note);
        }

        return $share;
    }
}
