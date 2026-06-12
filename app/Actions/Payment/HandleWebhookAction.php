<?php

namespace App\Actions\Payment;

use App\Models\Payment;
use App\Models\PaymentLog;
use Illuminate\Support\Facades\Log;

class HandleWebhookAction
{
    public function __construct(private VerifyPaymentAction $verify) {}

    public function execute(array $payload, string $gateway): void
    {
        $event     = $payload['event'] ?? 'unknown';
        $reference = $payload['data']['reference'] ?? null;

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
            $payment = Payment::where('gateway_reference', $reference)
                ->orWhere('reference', $reference)
                ->first();

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
        }
    }
}
