<?php

namespace App\Actions\Payment;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Services\Payment\PaymentManager;
use Illuminate\Support\Facades\DB;

class InitiateRefundAction
{
    public function __construct(private PaymentManager $manager) {}

    public function execute(Payment $payment, int $amountKobo = 0, string $reason = ''): Payment
    {
        abort_unless($payment->canBeRefunded(), 422, 'This payment cannot be refunded.');

        $refundAmount = $amountKobo > 0 ? $amountKobo : $payment->amount;

        return DB::transaction(function () use ($payment, $refundAmount, $reason) {
            $gateway = $this->manager->driver($payment->gateway->value);
            $success = $gateway->refund($payment->gateway_reference, $refundAmount);

            abort_unless($success, 422, 'Refund failed. Please try again or contact support.');

            $payment->update([
                'status'        => PaymentStatus::Refunded,
                'refunded_at'   => now(),
                'refund_amount' => $refundAmount,
                'refund_reason' => $reason,
            ]);

            PaymentLog::create([
                'payment_id'        => $payment->id,
                'event'             => 'refund_initiated',
                'gateway'           => $payment->gateway->value,
                'gateway_reference' => $payment->gateway_reference,
                'payload'           => ['refund_amount' => $refundAmount, 'reason' => $reason],
                'processed'         => true,
                'created_at'        => now(),
            ]);

            return $payment->fresh();
        });
    }
}
