<?php

namespace App\Actions\Chargebacks;

use App\Enums\ChargebackStatus;
use App\Enums\TransactionType;
use App\Models\Chargeback;
use App\Models\Escrow;
use App\Models\Payment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletLedger;
use App\Models\WalletReserve;
use App\Notifications\ChargebackOpenedNotification;
use App\Services\Escrow\EscrowService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class OpenChargebackAction
{
    public function __construct(
        private EscrowService $escrowService,
        private WalletService $walletService,
    ) {}

    /**
     * Open a chargeback against a payment. If the linked escrow is still held,
     * the funds are frozen (like a dispute). If the money has already been paid
     * out, the vendor's earnings are clawed back — reserve first, then spendable
     * balance — with any shortfall recorded. Idempotent per gateway reference.
     */
    public function execute(
        Payment $payment,
        ?string $gatewayReference = null,
        ?int $amountKobo = null,
        ?string $reason = null,
    ): Chargeback {
        $gatewayReference ??= $payment->gateway_reference ?? $payment->reference;

        $existing = Chargeback::where('payment_id', $payment->id)
            ->when($gatewayReference, fn ($q) => $q->orWhere('gateway_reference', $gatewayReference))
            ->first();

        if ($existing) {
            return $existing;
        }

        $escrow = Escrow::where('payment_id', $payment->id)->first();
        $amount = $amountKobo ?? $escrow?->total_amount ?? $payment->amount;

        return DB::transaction(function () use ($payment, $escrow, $gatewayReference, $amount, $reason) {
            $chargeback = Chargeback::create([
                'payment_id'        => $payment->id,
                'escrow_id'         => $escrow?->id,
                'buyer_id'          => $escrow?->buyer_id,
                'vendor_id'         => $escrow?->vendor_id,
                'gateway_reference' => $gatewayReference,
                'amount'            => $amount,
                'reason'            => $reason,
                'status'            => ChargebackStatus::Open,
            ]);

            if ($escrow && $escrow->canDispute()) {
                // Funds still held — freeze them exactly like a dispute.
                $this->escrowService->dispute($escrow, null, "Chargeback {$chargeback->reference}");
            } elseif ($escrow && $escrow->vendor) {
                // Already paid out — claw the vendor's earnings back.
                $this->clawback($chargeback, $escrow);
            }

            $this->notify($chargeback);

            return $chargeback->fresh();
        });
    }

    /** Recover the vendor's released earnings: reserve balance first, then spendable. */
    private function clawback(Chargeback $chargeback, Escrow $escrow): void
    {
        $target = (int) $escrow->vendor_earnings;
        if ($target <= 0) {
            return;
        }

        $wallet = $this->walletService->getOrCreate($escrow->vendor->user);

        // 1) Pull from the non-spendable reserve.
        $fromReserve = min($target, (int) ($wallet->reserve_balance ?? 0));
        if ($fromReserve > 0) {
            $this->walletService->decrementReserve($wallet, $fromReserve);
            $this->consumeReserveSlices($escrow, $wallet, $fromReserve);
        }

        // 2) Pull the remainder from spendable balance (capped, may leave a shortfall).
        $remaining   = $target - $fromReserve;
        $wallet      = $wallet->fresh();
        $fromBalance = max(0, min($remaining, $wallet->availableBalance()));

        if ($fromBalance > 0) {
            $newBalance = $wallet->balance - $fromBalance;
            $wallet->update(['balance' => $newBalance]);

            WalletLedger::create([
                'wallet_id'       => $wallet->id,
                'type'            => TransactionType::Chargeback,
                'amount'          => $fromBalance,
                'balance_after'   => $newBalance,
                'description'     => "Chargeback clawback for {$chargeback->reference}",
                'metadata'        => ['chargeback_reference' => $chargeback->reference],
                'ledgerable_type' => Chargeback::class,
                'ledgerable_id'   => $chargeback->id,
            ]);
        }

        $recovered = $fromReserve + $fromBalance;

        $chargeback->update([
            'clawed_back_amount' => $recovered,
            'unrecovered_amount' => max(0, $target - $recovered),
        ]);
    }

    /** Mark held reserve slices as clawed back, preferring ones tied to this escrow. */
    private function consumeReserveSlices(Escrow $escrow, Wallet $wallet, int $amount): void
    {
        $slices = WalletReserve::held()
            ->where('wallet_id', $wallet->id)
            ->orderByRaw('escrow_id = ? desc', [$escrow->id])
            ->oldest()
            ->get();

        foreach ($slices as $slice) {
            if ($amount <= 0) {
                break;
            }

            if ($slice->amount <= $amount) {
                $amount -= $slice->amount;
                $slice->update(['status' => 'clawed_back', 'clawed_back_at' => now()]);
            } else {
                // Partial consumption — shrink the slice, leave it held.
                $slice->update(['amount' => $slice->amount - $amount]);
                $amount = 0;
            }
        }
    }

    private function notify(Chargeback $chargeback): void
    {
        if ($chargeback->vendor && $chargeback->vendor->user) {
            $chargeback->vendor->user->notify(new ChargebackOpenedNotification($chargeback));
        }

        $admins = User::role('admin')->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new ChargebackOpenedNotification($chargeback));
        }
    }
}
