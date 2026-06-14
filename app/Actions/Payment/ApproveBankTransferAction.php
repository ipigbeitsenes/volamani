<?php

namespace App\Actions\Payment;

use App\Enums\BankTransferStatus;
use App\Enums\PaymentStatus;
use App\Models\BankTransferProof;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApproveBankTransferAction
{
    public function __construct(private FulfillPaymentAction $fulfill) {}

    public function approve(BankTransferProof $proof, User $admin): Payment
    {
        return DB::transaction(function () use ($proof, $admin) {
            $proof->update([
                'status'      => BankTransferStatus::Approved,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            $payment = $proof->payment;

            // Idempotent: only mark paid + fulfil once.
            if ($payment->status !== PaymentStatus::Success) {
                $payment->update([
                    'status'            => PaymentStatus::Success,
                    'gateway_reference' => 'BT-' . $proof->id,
                    'paid_at'           => now(),
                ]);

                // Canonical fulfilment — opens escrow, credits wallet fundings,
                // activates subscriptions, decrements stock, records affiliate
                // conversions, etc. (shared with gateway verification).
                $this->fulfill->execute($payment->fresh());
            }

            return $payment->fresh();
        });
    }

    public function reject(BankTransferProof $proof, User $admin, string $reason): void
    {
        DB::transaction(function () use ($proof, $admin, $reason) {
            $proof->update([
                'status'           => BankTransferStatus::Rejected,
                'reviewed_by'      => $admin->id,
                'reviewed_at'      => now(),
                'rejection_reason' => $reason,
            ]);

            $proof->payment->update(['status' => PaymentStatus::Failed, 'failed_at' => now()]);
        });
    }
}
