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

class ReleaseEscrowAction
{
    public function __construct(private WalletService $walletService) {}

    /**
     * Release held funds to the vendor's spendable wallet balance.
     * Pass $amountKobo for a partial release; null releases everything remaining.
     */
    public function execute(Escrow $escrow, ?int $amountKobo = null, ?User $actor = null): Escrow
    {
        abort_unless($escrow->canRelease(), 422, 'This escrow cannot be released at this stage.');

        $releasable = $escrow->releasableAmount();
        $amount     = $amountKobo === null ? $releasable : min($amountKobo, $releasable);

        abort_if($amount <= 0, 422, 'There is nothing left to release in this escrow.');

        return DB::transaction(function () use ($escrow, $amount, $actor, $releasable) {
            $locked = Escrow::where('id', $escrow->id)->lockForUpdate()->first();

            $vendorWallet = $locked->wallet ?? $this->walletService->getOrCreate($locked->vendor->user);

            // Move from pending escrow balance into spendable balance.
            $this->walletService->decrementEscrow($vendorWallet, $amount);
            $this->walletService->credit(
                $vendorWallet,
                $amount,
                TransactionType::EscrowRelease,
                "Escrow release for {$locked->reference}",
                $locked,
                ['escrow_reference' => $locked->reference]
            );

            $newReleased = $locked->released_amount + $amount;
            $fullyReleased = $newReleased >= $locked->vendor_earnings - $locked->refunded_amount;

            $locked->update([
                'released_amount' => $newReleased,
                'status'          => $fullyReleased ? EscrowStatus::Released : EscrowStatus::PartiallyReleased,
                'released_at'     => $fullyReleased ? now() : $locked->released_at,
                'auto_release_at' => null,
            ]);

            EscrowTransaction::create([
                'escrow_id'     => $locked->id,
                'type'          => EscrowTransactionType::Release,
                'amount'        => $amount,
                'balance_after' => $locked->fresh()->heldAmount(),
                'description'   => "Released " . money($amount) . " to vendor for {$locked->reference}",
                'actor_id'      => $actor?->id,
            ]);

            return $locked->fresh();
        });
    }
}
