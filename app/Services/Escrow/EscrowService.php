<?php

namespace App\Services\Escrow;

use App\Actions\Escrow\DisputeEscrowAction;
use App\Actions\Escrow\HoldEscrowAction;
use App\Actions\Escrow\RefundEscrowAction;
use App\Actions\Escrow\ReleaseEscrowAction;
use App\Models\Escrow;
use App\Models\Payment;
use App\Models\User;
use App\Repositories\Escrow\EscrowRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class EscrowService
{
    public function __construct(
        private HoldEscrowAction $holdAction,
        private ReleaseEscrowAction $releaseAction,
        private RefundEscrowAction $refundAction,
        private DisputeEscrowAction $disputeAction,
        private EscrowRepository $repo,
    ) {}

    /**
     * Open an escrow for a freshly-paid payable. Safe to call from the payment
     * fulfilment flow — idempotent and only acts on supported payable types.
     */
    public function holdForPayable(Model $payable, ?Payment $payment = null): ?Escrow
    {
        return $this->holdAction->execute($payable, $payment);
    }

    /** Release the escrow attached to a payable, if one exists and is releasable. */
    public function releaseForPayable(Model $payable, ?User $actor = null): ?Escrow
    {
        $escrow = $this->forPayable($payable);

        if ($escrow && $escrow->canRelease()) {
            return $this->releaseAction->execute($escrow, null, $actor);
        }

        return $escrow;
    }

    public function release(Escrow $escrow, ?int $amountKobo = null, ?User $actor = null): Escrow
    {
        return $this->releaseAction->execute($escrow, $amountKobo, $actor);
    }

    public function refund(Escrow $escrow, ?User $actor = null, ?string $reason = null): Escrow
    {
        return $this->refundAction->execute($escrow, $actor, $reason);
    }

    public function dispute(Escrow $escrow, ?User $actor = null, ?string $reason = null): Escrow
    {
        return $this->disputeAction->execute($escrow, $actor, $reason);
    }

    public function forPayable(Model $payable): ?Escrow
    {
        return Escrow::where('escrowable_type', get_class($payable))
            ->where('escrowable_id', $payable->getKey())
            ->first();
    }

    /** Release every escrow whose buyer-protection window has elapsed. Returns count released. */
    public function processAutoReleases(): int
    {
        $count = 0;

        foreach ($this->repo->dueForAutoRelease() as $escrow) {
            try {
                $this->releaseAction->execute($escrow, null, null);
                $count++;
            } catch (\Throwable $e) {
                Log::error("Escrow auto-release failed for {$escrow->reference}: {$e->getMessage()}");
            }
        }

        return $count;
    }

    // ─── Query passthroughs ─────────────────────────────────────────────────────

    public function buyerEscrows(User $user, int $perPage = 15)
    {
        return $this->repo->buyerEscrows($user, $perPage);
    }

    public function vendorEscrows($vendor, int $perPage = 15)
    {
        return $this->repo->vendorEscrows($vendor, $perPage);
    }

    public function allForAdmin(int $perPage = 20, array $filters = [])
    {
        return $this->repo->allForAdmin($perPage, $filters);
    }

    public function heldTotalForVendor($vendor): int
    {
        return $this->repo->heldTotalForVendor($vendor);
    }
}
