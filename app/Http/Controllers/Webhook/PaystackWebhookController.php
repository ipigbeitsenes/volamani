<?php

namespace App\Http\Controllers\Webhook;

use App\Actions\Payment\HandleWebhookAction;
use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PaystackWebhookController extends Controller
{
    public function __construct(
        private PaymentManager    $manager,
        private HandleWebhookAction $handler,
    ) {}

    public function handle(Request $request): Response
    {
        $signature = $request->header('x-paystack-signature');
        $payload   = $request->getContent();

        // Verify signature
        if (!$this->manager->paystack()->verifyWebhookSignature($payload, $signature ?? '')) {
            Log::warning('Paystack webhook: invalid signature', ['ip' => $request->ip()]);
            return response('Unauthorized', 401);
        }

        $data = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response('Bad Request', 400);
        }

        try {
            $this->handler->execute($data, 'paystack');
        } catch (\Throwable $e) {
            Log::error('Paystack webhook handler threw: ' . $e->getMessage(), [
                'payload' => $data,
            ]);
        }

        // Always return 200 — Paystack retries on non-200
        return response('OK', 200);
    }
}
