<?php

namespace App\Actions\Escrow;

use App\Enums\EscrowStatus;
use App\Enums\EscrowTransactionType;
use App\Enums\TransactionType;
use App\Models\Escrow;
use App\Models\EscrowTransaction;
use App\Models\User;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class RefundEscrowAction
{
    public function __construct(private WalletService $walletService) {}

    /**
     * Refund all funds still held in escrow back to the buyer's wallet (store
     * credit). The vendor's pending earnings for the held portion are reversed.
     * Money already released to the vendor is not clawed back.
     */
    public function execute(Escrow $escrow, ?User $actor = null, ?string $reason = null): Escrow
    {
        abort_unless($escrow->canRefund(), 422, 'This escrow cannot be refunded at this stage.');

        return DB::transaction(function () use ($escrow, $actor, $reason) {
            $locked = Escrow::where('id', $escrow->id)->lockForUpdate()->first();

            $buyerRefund = $locked->refundableAmount();   // total basis, fee-inclusive
            $vendorHeld = $locked->heldAmount();          // vendor basis
            $buyerWallet = $this->walletService->getOrCreate($locked->buyer);

            // Reverse the vendor's pending escrow earnings for the held portion.
            if ($vendorHeld > 0) {
                $vendorWallet = $locked->wallet ?? $this->walletService->getOrCreate($locked->vendor->user);
                $this->walletService->decrementEscrow($vendorWallet, $vendorHeld);
            }

            // Return the buyer's money as wallet credit.
            $this->walletService->credit(
                $buyerWallet,
                $buyerRefund,
                TransactionType::Refund,
                "Escrow refund for {$locked->reference}".($reason ? " — {$reason}" : ''),
                $locked,
                ['escrow_reference' => $locked->reference, 'reason' => $reason]
            );

            $locked->update([
                'refunded_amount' => $locked->refunded_amount + $buyerRefund,
                'status' => EscrowStatus::Refunded,
                'refunded_at' => now(),
                'auto_release_at' => null,
                'notes' => $reason ?? $locked->notes,
            ]);

            EscrowTransaction::create([
                'escrow_id' => $locked->id,
                'type' => EscrowTransactionType::Refund,
                'amount' => $buyerRefund,
                'balance_after' => 0,
                'description' => 'Refunded '.money($buyerRefund)." to buyer for {$locked->reference}",
                'actor_id' => $actor?->id,
                'metadata' => ['reason' => $reason],
            ]);

            return $locked->fresh();
        });
    }
}
