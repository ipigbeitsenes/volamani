<?php

namespace App\Services\Chargebacks;

use App\Actions\Chargebacks\ContestChargebackAction;
use App\Actions\Chargebacks\OpenChargebackAction;
use App\Actions\Chargebacks\ResolveChargebackAction;
use App\Enums\ChargebackStatus;
use App\Models\Chargeback;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vendor;
use App\Repositories\Chargebacks\ChargebackRepository;
use App\Services\BaseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ChargebackService extends BaseService
{
    public function __construct(
        private ChargebackRepository     $repo,
        private OpenChargebackAction     $openAction,
        private ContestChargebackAction  $contestAction,
        private ResolveChargebackAction  $resolveAction,
    ) {}

    public function open(Payment $payment, ?string $gatewayReference = null, ?int $amountKobo = null, ?string $reason = null): Chargeback
    {
        return $this->openAction->execute($payment, $gatewayReference, $amountKobo, $reason);
    }

    public function contest(Chargeback $chargeback, User $actor, ?string $note, array $files = []): Chargeback
    {
        return $this->contestAction->execute($chargeback, $actor, $note, $files);
    }

    public function resolve(Chargeback $chargeback, ChargebackStatus $outcome, ?User $admin = null, ?string $note = null): Chargeback
    {
        return $this->resolveAction->execute($chargeback, $outcome, $admin, $note);
    }

    /**
     * Settle a chargeback from a gateway webhook payload. Paystack reports the
     * winner via data.status ('resolved' → merchant won; 'declined'/'lost' →
     * buyer won). Anything ambiguous is treated as lost (buyer-protective).
     */
    public function settle(Chargeback $chargeback, ?string $gatewayStatus, ?string $note = null): Chargeback
    {
        if (! $chargeback->canResolve()) {
            return $chargeback;
        }

        $won = in_array(strtolower((string) $gatewayStatus), ['resolved', 'merchant-accepted', 'won'], true);

        return $this->resolve(
            $chargeback,
            $won ? ChargebackStatus::Won : ChargebackStatus::Lost,
            null,
            $note ?? "Auto-settled from gateway (status: {$gatewayStatus})",
        );
    }

    // ─── Queries ────────────────────────────────────────────────────────────────

    public function forVendor(Vendor $vendor, array $filters = []): LengthAwarePaginator
    {
        return $this->repo->forVendor($vendor, $filters);
    }

    public function allForAdmin(array $filters = []): LengthAwarePaginator
    {
        return $this->repo->allForAdmin($filters);
    }

    public function openCount(): int
    {
        return $this->repo->openCount();
    }

    public function openCountForVendor(Vendor $vendor): int
    {
        return $this->repo->openCountForVendor($vendor);
    }
}
