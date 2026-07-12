<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessGatewayWebhookJob;
use App\Services\Payment\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PaystackWebhookController extends Controller
{
    public function __construct(
        private PaymentManager $manager,
    ) {}

    public function handle(Request $request): Response
    {
        $signature = $request->header('x-paystack-signature');
        $payload = $request->getContent();

        // Verify signature
        if (! $this->manager->paystack()->verifyWebhookSignature($payload, $signature ?? '')) {
            Log::warning('Paystack webhook: invalid signature', ['ip' => $request->ip()]);

            return response('Unauthorized', 401);
        }

        $data = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response('Bad Request', 400);
        }

        // Hand off to the queue and ACK immediately. Verification + fulfilment can
        // be slow (gateway round-trip, escrow, mail); doing it inline risks
        // timing out the response Paystack is waiting on. The job retries with
        // back-off and dead-letters to failed_jobs — no silently-lost events.
        // If enqueue itself fails the exception yields a 5xx, so Paystack retries.
        ProcessGatewayWebhookJob::dispatch($data, 'paystack');

        return response('OK', 200);
    }
}
