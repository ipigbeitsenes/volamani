<?php

namespace App\Actions\Escrow;

use App\Enums\EscrowStatus;
use App\Enums\EscrowTransactionType;
use App\Models\Escrow;
use App\Models\EscrowTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DisputeEscrowAction
{
    /**
     * Freeze an escrow pending dispute resolution (Module 11). Funds stay held —
     * auto-release is cancelled so nothing pays out until an admin releases or refunds.
     */
    public function execute(Escrow $escrow, ?User $actor = null, ?string $reason = null): Escrow
    {
        abort_unless($escrow->canDispute(), 422, 'This escrow can no longer be disputed.');

        return DB::transaction(function () use ($escrow, $actor, $reason) {
            $locked = Escrow::where('id', $escrow->id)->lockForUpdate()->first();

            $locked->update([
                'status'          => EscrowStatus::Disputed,
                'disputed_at'     => now(),
                'auto_release_at' => null,
                'notes'           => $reason ?? $locked->notes,
            ]);

            EscrowTransaction::create([
                'escrow_id'     => $locked->id,
                'type'          => EscrowTransactionType::Dispute,
                'amount'        => $locked->heldAmount(),
                'balance_after' => $locked->heldAmount(),
                'description'   => "Escrow {$locked->reference} marked disputed — funds frozen",
                'actor_id'      => $actor?->id,
                'metadata'      => ['reason' => $reason],
            ]);

            return $locked->fresh();
        });
    }
}
