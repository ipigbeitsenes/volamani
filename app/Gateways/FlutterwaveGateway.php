<?php

namespace App\Gateways;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Flutterwave payment gateway — a drop-in alternative/backup to Paystack behind
 * the same PaymentGatewayInterface. Note Flutterwave works in MAJOR currency
 * units (naira), so amounts are converted from the platform's minor units (kobo)
 * on the way out and back on the way in.
 */
class FlutterwaveGateway implements PaymentGatewayInterface
{
    private string $secretKey;

    private string $secretHash;

    private string $baseUrl;

    private string $callbackUrl;

    public function __construct()
    {
        $this->secretKey = (string) config('payment.flutterwave.secret_key');
        $this->secretHash = (string) config('payment.flutterwave.secret_hash');
        $this->baseUrl = (string) config('payment.flutterwave.base_url');
        $this->callbackUrl = (string) config('payment.flutterwave.callback_url');
    }

    public function initiate(int $amountKobo, string $email, string $reference, array $metadata = []): array
    {
        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/payments", [
                'tx_ref' => $reference,
                'amount' => round($amountKobo / 100, 2),          // kobo → naira
                'currency' => currency_code(),
                'redirect_url' => $this->callbackUrl,
                'payment_options' => 'card,banktransfer,ussd',
                'customer' => ['email' => $email],
                'meta' => $metadata,
            ]);

        if (! $response->successful() || $response->json('status') !== 'success') {
            Log::error('Flutterwave initiate failed', ['response' => $response->json()]);
            throw new \RuntimeException('Payment gateway error: '.($response->json('message') ?? 'Unknown error'));
        }

        return [
            'authorization_url' => $response->json('data.link'),
            'access_code' => '',
            'reference' => $reference,
        ];
    }

    public function verify(string $gatewayReference): array
    {
        $data = $this->fetchTransaction($gatewayReference);

        return [
            'status' => match ($data['status'] ?? null) {
                'successful' => 'success',
                'failed' => 'failed',
                default => 'pending',
            },
            'amount' => (int) round(((float) ($data['amount'] ?? 0)) * 100),   // naira → kobo
            'reference' => $data['tx_ref'] ?? $gatewayReference,
            'paid_at' => $data['created_at'] ?? null,
            'metadata' => $data,
        ];
    }

    public function refund(string $gatewayReference, int $amountKobo): bool
    {
        $data = $this->fetchTransaction($gatewayReference);
        $id = $data['id'] ?? null;

        if (! $id) {
            Log::error('Flutterwave refund: transaction not found', ['reference' => $gatewayReference]);

            return false;
        }

        $body = $amountKobo > 0 ? ['amount' => round($amountKobo / 100, 2)] : [];

        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/transactions/{$id}/refund", $body);

        if (! $response->successful() || $response->json('status') !== 'success') {
            Log::error('Flutterwave refund failed', ['reference' => $gatewayReference, 'response' => $response->json()]);

            return false;
        }

        return true;
    }

    /** Flutterwave signs webhooks with a static "verif-hash" (the secret hash). */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        return $this->secretHash !== '' && hash_equals($this->secretHash, $signature);
    }

    public function getName(): string
    {
        return 'flutterwave';
    }

    /** Look up a transaction by our tx_ref (the payment reference). */
    private function fetchTransaction(string $reference): array
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transactions/verify_by_reference", ['tx_ref' => $reference]);

        if (! $response->successful() || $response->json('status') !== 'success') {
            Log::error('Flutterwave verify failed', ['reference' => $reference, 'response' => $response->json()]);
            throw new \RuntimeException('Payment verification failed: '.($response->json('message') ?? 'Unknown error'));
        }

        return (array) $response->json('data');
    }
}
