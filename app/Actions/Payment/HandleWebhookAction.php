<?php

namespace App\Actions\Payment;

use App\Models\Chargeback;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Services\Chargebacks\ChargebackService;
use Illuminate\Support\Facades\Log;

class HandleWebhookAction
{
    public function __construct(
        private VerifyPaymentAction $verify,
        private ChargebackService   $chargebacks,
    ) {}

    public function execute(array $payload, string $gateway): void
    {
        $event     = $payload['event'] ?? 'unknown';
        $data      = $payload['data'] ?? [];
        $reference = $data['reference'] ?? null;

        // Log every incoming webhook for auditing
        $log = PaymentLog::create([
            'event'             => 'webhook_received',
            'gateway'           => $gateway,
            'gateway_reference' => $reference,
            'payload'           => $payload,
            'ip_address'        => request()->ip(),
            'processed'         => false,
            'created_at'        => now(),
        ]);

        // Idempotency: skip if this reference was already processed
        if ($reference && PaymentLog::where('gateway_reference', $reference)
            ->where('event', 'payment_verified')
            ->where('processed', true)
            ->exists()) {
            Log::info("Webhook duplicate skipped: {$reference}");
            return;
        }

        if ($event === 'charge.success') {
            $payment = $this->findPayment($reference);

            if (!$payment) {
                Log::warning("Webhook: no payment found for reference {$reference}");
                return;
            }

            try {
                $this->verify->execute($payment);
                $log->update(['payment_id' => $payment->id, 'processed' => true]);
            } catch (\Throwable $e) {
                Log::error("Webhook processing failed for {$reference}: " . $e->getMessage());
            }

            return;
        }

        // Paystack reports chargebacks as "disputes".
        if (in_array($event, ['charge.dispute.create', 'charge.dispute.remind'], true)) {
            $this->handleDisputeOpened($data, $log);
            return;
        }

        if ($event === 'charge.dispute.resolve') {
            $this->handleDisputeResolved($data, $log);
            return;
        }
    }

    private function handleDisputeOpened(array $data, PaymentLog $log): void
    {
        $txnRef  = $data['transaction']['reference'] ?? $data['reference'] ?? null;
        $payment = $this->findPayment($txnRef);

        if (!$payment) {
            Log::warning("Chargeback webhook: no payment found for reference {$txnRef}");
            return;
        }

        try {
            $amount = (int) ($data['transaction']['amount'] ?? $data['refund_amount'] ?? $payment->amount);
            $reason = $data['category'] ?? $data['reason'] ?? null;

            $this->chargebacks->open($payment, $txnRef, $amount, $reason);
            $log->update(['payment_id' => $payment->id, 'processed' => true]);
        } catch (\Throwable $e) {
            Log::error("Chargeback open failed for {$txnRef}: " . $e->getMessage());
        }
    }

    private function handleDisputeResolved(array $data, PaymentLog $log): void
    {
        $txnRef     = $data['transaction']['reference'] ?? $data['reference'] ?? null;
        $payment    = $this->findPayment($txnRef);
        $chargeback = $payment ? Chargeback::where('payment_id', $payment->id)->first() : null;

        if (!$chargeback) {
            Log::warning("Chargeback resolve webhook: no chargeback found for reference {$txnRef}");
            return;
        }

        try {
            $this->chargebacks->settle($chargeback, $data['status'] ?? null);
            $log->update(['payment_id' => $payment->id, 'processed' => true]);
        } catch (\Throwable $e) {
            Log::error("Chargeback resolve failed for {$txnRef}: " . $e->getMessage());
        }
    }

    private function findPayment(?string $reference): ?Payment
    {
        if (!$reference) {
            return null;
        }

        return Payment::where('gateway_reference', $reference)
            ->orWhere('reference', $reference)
            ->first();
    }
}
