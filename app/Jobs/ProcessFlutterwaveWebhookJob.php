<?php

namespace App\Jobs;

use App\Services\Payment\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

/**
 * Processes a verified Flutterwave webhook off the request thread — verifies the
 * transaction by its reference (idempotent via VerifyPaymentAction) and fulfils.
 * Retries with back-off and dead-letters to failed_jobs, like the Paystack path.
 */
class ProcessFlutterwaveWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 120;

    public function __construct(public string $reference) {}

    public function backoff(): array
    {
        return [10, 30, 60, 120];
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping("webhook:flutterwave:{$this->reference}"))->expireAfter(180)];
    }

    public function handle(PaymentService $payments): void
    {
        $payments->verifyByReference($this->reference);
    }
}
