<?php

namespace App\Actions\Escrow;

use App\Enums\EscrowStatus;
use App\Enums\EscrowTransactionType;
use App\Models\ConsultationSession;
use App\Models\Escrow;
use App\Models\EscrowTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ServiceOrder;
use App\Services\Wallet\WalletService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HoldEscrowAction
{
    public function __construct(private WalletService $walletService) {}

    /**
     * Open an escrow holding the buyer's funds against a paid payable.
     * Idempotent: returns the existing escrow if one already exists for this payable.
     */
    public function execute(Model $escrowable, ?Payment $payment = null): ?Escrow
    {
        $existing = Escrow::where('escrowable_type', get_class($escrowable))
            ->where('escrowable_id', $escrowable->getKey())
            ->first();

        if ($existing) {
            return $existing;
        }

        $details = $this->resolve($escrowable);
        if (!$details || !$details['vendor'] || !$details['vendor']->user) {
            return null;
        }

        return DB::transaction(function () use ($escrowable, $payment, $details) {
            $vendorWallet = $this->walletService->getOrCreate($details['vendor']->user);

            $escrow = Escrow::create([
                'escrowable_type' => get_class($escrowable),
                'escrowable_id'   => $escrowable->getKey(),
                'buyer_id'        => $details['buyer_id'],
                'vendor_id'       => $details['vendor']->id,
                'wallet_id'       => $vendorWallet->id,
                'payment_id'      => $payment?->id,
                'total_amount'    => $details['total'],
                'platform_fee'    => $details['fee'],
                'vendor_earnings' => $details['earnings'],
                'status'          => EscrowStatus::Holding,
                'auto_release_at' => $this->autoReleaseAt($escrowable),
                'held_at'         => now(),
            ]);

            // Reflect the pending earnings on the vendor's wallet (not spendable yet).
            $this->walletService->incrementEscrow($vendorWallet, $details['earnings']);

            EscrowTransaction::create([
                'escrow_id'     => $escrow->id,
                'type'          => EscrowTransactionType::Hold,
                'amount'        => $details['earnings'],
                'balance_after' => $details['earnings'],
                'description'   => "Funds held in escrow for {$escrow->reference}",
            ]);

            return $escrow;
        });
    }

    /**
     * Extract [total, fee, earnings, vendor, buyer_id] from each payable type.
     */
    private function resolve(Model $escrowable): ?array
    {
        return match (true) {
            $escrowable instanceof Order => [
                'total'    => (int) $escrowable->total_amount,
                'fee'      => (int) $escrowable->platform_fee,
                'earnings' => (int) $escrowable->vendor_earnings,
                'vendor'   => $escrowable->vendor,
                'buyer_id' => $escrowable->buyer_id,
            ],
            $escrowable instanceof ServiceOrder => [
                'total'    => (int) $escrowable->total_amount,
                'fee'      => (int) $escrowable->platform_fee,
                'earnings' => (int) $escrowable->vendor_earnings,
                'vendor'   => $escrowable->vendor,
                'buyer_id' => $escrowable->buyer_id,
            ],
            $escrowable instanceof ConsultationSession => [
                'total'    => (int) $escrowable->price,
                'fee'      => (int) $escrowable->platform_fee,
                'earnings' => (int) $escrowable->consultant_earnings,
                'vendor'   => $escrowable->profile?->vendor,
                'buyer_id' => $escrowable->buyer_id,
            ],
            default => null,
        };
    }

    /**
     * Digital products auto-release after a buyer-protection window measured in
     * business working days (weekends + Nigerian public holidays skipped), unless
     * a support ticket freezes the funds first. Service orders and consultations
     * release on explicit completion (no timed auto-release).
     */
    private function autoReleaseAt(Model $escrowable): ?\Illuminate\Support\Carbon
    {
        if ($escrowable instanceof Order) {
            $days = (int) config('business_days.release_days', 3);
            return app(\App\Support\BusinessDayCalculator::class)
                ->addBusinessDays(now(), max(1, $days));
        }

        return null;
    }
}
