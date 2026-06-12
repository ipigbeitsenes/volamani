<?php

namespace App\Gateways;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackGateway implements PaymentGatewayInterface
{
    private string $secretKey;
    private string $baseUrl;
    private string $callbackUrl;

    public function __construct()
    {
        $this->secretKey   = config('payment.paystack.secret_key');
        $this->baseUrl     = config('payment.paystack.base_url');
        $this->callbackUrl = config('payment.paystack.callback_url');
    }

    public function initiate(int $amountKobo, string $email, string $reference, array $metadata = []): array
    {
        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/transaction/initialize", [
                'email'        => $email,
                'amount'       => $amountKobo,
                'reference'    => $reference,
                'callback_url' => $this->callbackUrl,
                'metadata'     => array_merge($metadata, ['cancel_action' => url()->previous()]),
            ]);

        if (!$response->successful() || !$response->json('status')) {
            Log::error('Paystack initiate failed', ['response' => $response->json()]);
            throw new \RuntimeException('Payment gateway error: ' . ($response->json('message') ?? 'Unknown error'));
        }

        return $response->json('data');
    }

    public function verify(string $gatewayReference): array
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transaction/verify/{$gatewayReference}");

        if (!$response->successful() || !$response->json('status')) {
            Log::error('Paystack verify failed', ['reference' => $gatewayReference, 'response' => $response->json()]);
            throw new \RuntimeException('Payment verification failed: ' . ($response->json('message') ?? 'Unknown error'));
        }

        $data = $response->json('data');

        return [
            'status'    => $data['status'],            // success|failed|abandoned|reversed
            'amount'    => (int) $data['amount'],      // kobo
            'reference' => $data['reference'],
            'paid_at'   => $data['paid_at'] ?? null,
            'metadata'  => $data,
        ];
    }

    public function refund(string $gatewayReference, int $amountKobo): bool
    {
        $body = ['transaction' => $gatewayReference];
        if ($amountKobo > 0) {
            $body['amount'] = $amountKobo;
        }

        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/refund", $body);

        if (!$response->successful() || !$response->json('status')) {
            Log::error('Paystack refund failed', ['reference' => $gatewayReference, 'response' => $response->json()]);
            return false;
        }

        return true;
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $computed = hash_hmac('sha512', $payload, $this->secretKey);
        return hash_equals($computed, $signature);
    }

    public function getName(): string
    {
        return 'paystack';
    }
}
