<?php

namespace App\Jobs;

use App\Actions\Payment\HandleWebhookAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

/**
 * Processes a verified gateway webhook off the request thread.
 *
 * The controller only checks the signature and hands the raw payload here, so a
 * slow verify/fulfilment (escrow, wallet, mail) can never stall — or time out —
 * the HTTP response Paystack is waiting on. Operational failures bubble up so the
 * queue retries them with back-off; after {@see $tries} they land in failed_jobs
 * for inspection instead of being silently swallowed and lost.
 */
class ProcessGatewayWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Retry a handful of times to ride out transient DB/gateway blips. */
    public int $tries = 5;

    /** Give up after 10 minutes of retries regardless of attempt count. */
    public int $timeout = 120;

    public function __construct(
        public array $payload,
        public string $gateway,
    ) {}

    /** Exponential-ish back-off between retries (seconds). */
    public function backoff(): array
    {
        return [10, 30, 60, 120];
    }

    /**
     * Serialise processing per gateway reference so two deliveries of the same
     * event can never be worked concurrently (belt-and-braces with the row lock
     * inside VerifyPaymentAction).
     */
    public function middleware(): array
    {
        $reference = $this->payload['data']['reference']
            ?? $this->payload['data']['transaction']['reference']
            ?? 'unknown';

        return [(new WithoutOverlapping("webhook:{$this->gateway}:{$reference}"))->expireAfter(180)];
    }

    public function handle(HandleWebhookAction $handler): void
    {
        $handler->execute($this->payload, $this->gateway);
    }
}
