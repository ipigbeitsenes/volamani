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
    public function __construct(private VerifyPaymentAction $verify) {}

    public function approve(BankTransferProof $proof, User $admin): Payment
    {
        return DB::transaction(function () use ($proof, $admin) {
            $proof->update([
                'status'      => BankTransferStatus::Approved,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            $payment = $proof->payment;
            $payment->update([
                'status'            => PaymentStatus::Success,
                'gateway_reference' => 'BT-' . $proof->id,
                'paid_at'           => now(),
            ]);

            // Trigger fulfillment (reuse VerifyPaymentAction's fulfill logic)
            $this->fulfillPayable($payment);

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

    private function fulfillPayable(Payment $payment): void
    {
        // Delegate to VerifyPaymentAction's private fulfillment by re-using the same logic
        // We mark as already verified so the gateway call is skipped
        $payment->update(['status' => PaymentStatus::Success]);
        $payable = $payment->payable;

        if (!$payable) return;

        if ($payable instanceof \App\Models\Order) {
            $payable->update([
                'payment_status'    => PaymentStatus::Success,
                'status'            => \App\Enums\OrderStatus::Paid,
                'payment_reference' => $payment->gateway_reference,
                'payment_method'    => 'bank_transfer',
                'paid_at'           => now(),
            ]);
        } elseif ($payable instanceof \App\Models\ServiceOrder) {
            $payable->update([
                'payment_status'    => PaymentStatus::Success,
                'status'            => \App\Enums\ServiceOrderStatus::Active,
                'payment_reference' => $payment->gateway_reference,
                'payment_method'    => 'bank_transfer',
                'paid_at'           => now(),
            ]);
        } elseif ($payable instanceof \App\Models\ConsultationSession) {
            $payable->update(['payment_status' => PaymentStatus::Success, 'paid_at' => now()]);
        }
    }
}
