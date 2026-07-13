<?php

namespace Tests\Feature;

use App\Gateways\FlutterwaveGateway;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FlutterwaveGatewayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'payment.flutterwave.secret_key' => 'FLWSECK_TEST',
            'payment.flutterwave.secret_hash' => 'my-verif-hash',
            'payment.flutterwave.base_url' => 'https://api.flutterwave.com/v3',
            'payment.flutterwave.callback_url' => 'https://volamani.com/checkout/callback',
        ]);
    }

    public function test_initiate_returns_the_hosted_checkout_link(): void
    {
        Http::fake([
            'api.flutterwave.com/v3/payments' => Http::response([
                'status' => 'success',
                'data' => ['link' => 'https://checkout.flutterwave.com/pay/abc123'],
            ]),
        ]);

        $result = (new FlutterwaveGateway)->initiate(500_000, 'buyer@example.com', 'REF-1', ['payment_id' => 7]);

        $this->assertSame('https://checkout.flutterwave.com/pay/abc123', $result['authorization_url']);
        $this->assertSame('REF-1', $result['reference']);
    }

    public function test_verify_maps_a_successful_transaction_to_base_minor_units(): void
    {
        Http::fake([
            'api.flutterwave.com/v3/transactions/verify_by_reference*' => Http::response([
                'status' => 'success',
                'data' => [
                    'id' => 99,
                    'tx_ref' => 'REF-1',
                    'status' => 'successful',
                    'amount' => 5000,               // naira
                    'created_at' => '2026-07-13T10:00:00Z',
                ],
            ]),
        ]);

        $result = (new FlutterwaveGateway)->verify('REF-1');

        $this->assertSame('success', $result['status']);
        $this->assertSame(500_000, $result['amount']);   // ₦5,000 → 500,000 kobo
        $this->assertSame('REF-1', $result['reference']);
    }

    public function test_webhook_signature_matches_the_configured_verif_hash(): void
    {
        $gateway = new FlutterwaveGateway;

        $this->assertTrue($gateway->verifyWebhookSignature('{}', 'my-verif-hash'));
        $this->assertFalse($gateway->verifyWebhookSignature('{}', 'wrong-hash'));
        $this->assertFalse($gateway->verifyWebhookSignature('{}', ''));
    }
}
