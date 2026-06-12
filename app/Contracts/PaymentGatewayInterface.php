<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Initialise a payment and return the redirect/checkout URL.
     *
     * @return array{authorization_url: string, access_code: string, reference: string}
     */
    public function initiate(int $amountKobo, string $email, string $reference, array $metadata = []): array;

    /**
     * Verify a completed transaction by its gateway reference.
     *
     * @return array{status: string, amount: int, reference: string, paid_at: string|null, metadata: array}
     */
    public function verify(string $gatewayReference): array;

    /**
     * Refund a transaction (full or partial).
     */
    public function refund(string $gatewayReference, int $amountKobo): bool;

    /**
     * Verify a webhook payload signature.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool;

    public function getName(): string;
}
