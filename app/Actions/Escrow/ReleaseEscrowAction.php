<?php

namespace App\Actions\Escrow;

use App\Enums\EscrowStatus;
use App\Enums\EscrowTransactionType;
use App\Enums\TransactionType;
use App\Models\Escrow;
use App\Models\EscrowTransaction;
use App\Models\User;
use App\Models\WalletReserve;
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
        $amount = $amountKobo === null ? $releasable : min($amountKobo, $releasable);

        abort_if($amount <= 0, 422, 'There is nothing left to release in this escrow.');

        return DB::transaction(function () use ($escrow, $amount, $actor) {
            $locked = Escrow::where('id', $escrow->id)->lockForUpdate()->first();

            $vendorWallet = $locked->wallet ?? $this->walletService->getOrCreate($locked->vendor->user);

            // Split off a rolling chargeback reserve (opt-in, config/settings driven).
            $reserve = $this->reserveFor($amount);
            $spendable = $amount - $reserve;

            // Move the whole released amount out of pending escrow balance...
            $this->walletService->decrementEscrow($vendorWallet, $amount);

            // ...credit the vendor's spendable balance with the net...
            if ($spendable > 0) {
                $this->walletService->credit(
                    $vendorWallet,
                    $spendable,
                    TransactionType::EscrowRelease,
                    "Escrow release for {$locked->reference}".($reserve > 0 ? ' (net of reserve)' : ''),
                    $locked,
                    ['escrow_reference' => $locked->reference, 'reserve_held' => $reserve]
                );
            }

            // ...and park the reserve slice in the non-spendable reserve balance.
            if ($reserve > 0) {
                $this->walletService->incrementReserve($vendorWallet, $reserve);
                WalletReserve::create([
                    'wallet_id' => $vendorWallet->id,
                    'vendor_id' => $locked->vendor_id,
                    'escrow_id' => $locked->id,
                    'amount' => $reserve,
                    'status' => 'held',
                    'release_at' => now()->addDays($this->reserveDays()),
                ]);
            }

            $newReleased = $locked->released_amount + $amount;
            $fullyReleased = $newReleased >= $locked->vendor_earnings - $locked->refunded_amount;

            $locked->update([
                'released_amount' => $newReleased,
                'status' => $fullyReleased ? EscrowStatus::Released : EscrowStatus::PartiallyReleased,
                'released_at' => $fullyReleased ? now() : $locked->released_at,
                'auto_release_at' => null,
            ]);

            EscrowTransaction::create([
                'escrow_id' => $locked->id,
                'type' => EscrowTransactionType::Release,
                'amount' => $amount,
                'balance_after' => $locked->fresh()->heldAmount(),
                'description' => 'Released '.money($amount)." to vendor for {$locked->reference}",
                'actor_id' => $actor?->id,
            ]);

            return $locked->fresh();
        });
    }

    /** Kobo to hold back as reserve for a given release amount. */
    private function reserveFor(int $amount): int
    {
        $pct = $this->reservePercent();

        return $pct > 0 ? (int) round($amount * $pct / 100) : 0;
    }

    private function reservePercent(): float
    {
        $v = settings('chargeback_reserve_percent');
        $pct = ($v === null || $v === '') ? (float) config('protection.reserve_percent', 0) : (float) $v;

        return max(0.0, min(100.0, $pct));
    }

    private function reserveDays(): int
    {
        $v = settings('chargeback_reserve_days');

        return (int) (($v === null || $v === '') ? config('protection.reserve_days', 30) : $v);
    }
}
