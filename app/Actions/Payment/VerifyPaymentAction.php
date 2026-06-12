<?php

namespace App\Actions\Payment;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Services\Payment\PaymentManager;
use Illuminate\Support\Facades\DB;

class VerifyPaymentAction
{
    public function __construct(
        private PaymentManager        $manager,
        private FulfillPaymentAction  $fulfillAction,
    ) {}

    public function execute(Payment $payment): Payment
    {
        if ($payment->isSuccessful()) {
            return $payment; // already verified — idempotent
        }

        return DB::transaction(function () use ($payment) {
            $gateway  = $this->manager->driver($payment->gateway->value);
            $result   = $gateway->verify($payment->gateway_reference);

            PaymentLog::create([
                'payment_id'        => $payment->id,
                'event'             => 'payment_verified',
                'gateway'           => $payment->gateway->value,
                'gateway_reference' => $payment->gateway_reference,
                'payload'           => $result,
                'processed'         => true,
                'created_at'        => now(),
            ]);

            $status = match ($result['status']) {
                'success'   => PaymentStatus::Success,
                'failed'    => PaymentStatus::Failed,
                'abandoned' => PaymentStatus::Abandoned,
                'reversed'  => PaymentStatus::Reversed,
                default     => PaymentStatus::Pending,
            };

            $payment->update([
                'status'   => $status,
                'metadata' => array_merge($payment->metadata ?? [], $result['metadata']),
                'paid_at'  => $status === PaymentStatus::Success ? now() : null,
                'failed_at' => $status === PaymentStatus::Failed ? now() : null,
            ]);

            if ($status === PaymentStatus::Success) {
                $this->fulfillAction->execute($payment);
            }

            return $payment->fresh();
        });
    }
}
