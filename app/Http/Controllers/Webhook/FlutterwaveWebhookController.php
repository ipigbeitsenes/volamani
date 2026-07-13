<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessFlutterwaveWebhookJob;
use App\Services\Payment\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class FlutterwaveWebhookController extends Controller
{
    public function __construct(private PaymentManager $manager) {}

    public function handle(Request $request): Response
    {
        // Flutterwave signs webhooks with a static "verif-hash" header.
        $signature = (string) $request->header('verif-hash', '');

        if (! $this->manager->flutterwave()->verifyWebhookSignature($request->getContent(), $signature)) {
            Log::warning('Flutterwave webhook: invalid signature', ['ip' => $request->ip()]);

            return response('Unauthorized', 401);
        }

        $reference = $request->json('data.tx_ref') ?? $request->json('data.reference');

        if ($reference) {
            // Hand off to the queue and ACK immediately (verify + fulfil can be slow).
            ProcessFlutterwaveWebhookJob::dispatch((string) $reference);
        }

        return response('OK', 200);
    }
}
