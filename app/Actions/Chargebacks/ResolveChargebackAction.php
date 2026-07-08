<?php

namespace App\Actions\Chargebacks;

use App\Actions\Vendors\AddStrikeAction;
use App\Enums\ChargebackStatus;
use App\Enums\EscrowStatus;
use App\Enums\StrikeReason;
use App\Enums\TransactionType;
use App\Models\Chargeback;
use App\Models\User;
use App\Services\Escrow\EscrowService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class ResolveChargebackAction
{
    public function __construct(
        private EscrowService   $escrowService,
        private WalletService   $walletService,
        private AddStrikeAction $addStrike,
    ) {}

    /**
     * Settle a chargeback. Won (merchant) restores the frozen escrow / re-credits
     * any clawed-back earnings. Lost (buyer) refunds still-held escrow, leaves the
     * clawback standing, and issues the vendor a strike. May be called by an admin
     * or by the gateway webhook (admin null).
     */
    public function execute(Chargeback $chargeback, ChargebackStatus $outcome, ?User $admin = null, ?string $note = null): Chargeback
    {
        abort_unless($chargeback->canResolve(), 422, 'This chargeback has already been resolved.');
        abort_unless(in_array($outcome, [ChargebackStatus::Won, ChargebackStatus::Lost], true), 422, 'Invalid chargeback outcome.');

        return DB::transaction(function () use ($chargeback, $outcome, $admin, $note) {
            $escrow = $chargeback->escrow;

            if ($outcome === ChargebackStatus::Won) {
                // Merchant won — undo the protective freeze / clawback.
                if ($escrow && $escrow->status === EscrowStatus::Disputed && $escrow->canRelease()) {
                    $this->escrowService->release($escrow, null, $admin);
                }

                if ($chargeback->clawed_back_amount > 0 && $chargeback->vendor && $chargeback->vendor->user) {
                    $wallet = $this->walletService->getOrCreate($chargeback->vendor->user);
                    $this->walletService->credit(
                        $wallet,
                        $chargeback->clawed_back_amount,
                        TransactionType::Credit,
                        "Chargeback {$chargeback->reference} won — clawback reversed",
                        $chargeback,
                        ['chargeback_reference' => $chargeback->reference]
                    );
                }
            } else {
                // Buyer won — refund any still-held escrow; clawback stands.
                if ($escrow && $escrow->canRefund()) {
                    $this->escrowService->refund($escrow, $admin, "Chargeback {$chargeback->reference} lost");
                }

                if ($chargeback->vendor) {
                    $this->addStrike->execute(
                        $chargeback->vendor,
                        StrikeReason::ChargebackLost,
                        "Chargeback {$chargeback->reference} lost",
                        $chargeback->id,
                        $admin,
                    );
                }
            }

            $chargeback->update([
                'status'          => $outcome,
                'resolved_by'     => $admin?->id,
                'resolution_note' => $note,
                'resolved_at'     => now(),
            ]);

            return $chargeback->fresh();
        });
    }
}
