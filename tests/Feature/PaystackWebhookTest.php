<?php

namespace Tests\Feature;

use App\Jobs\ProcessGatewayWebhookJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * The webhook edge contract: authenticate by HMAC signature, ACK fast, and hand
 * processing to the queue (never inline, so a slow verify can't stall the ACK
 * Paystack is waiting on).
 */
class PaystackWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $secret = 'sk_test_webhook_secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['payment.paystack.secret_key' => $this->secret]);
    }

    private function sign(string $payload): string
    {
        return hash_hmac('sha512', $payload, $this->secret);
    }

    private function postWebhook(array $body, ?string $signature = null)
    {
        $payload = json_encode($body);
        $signature ??= $this->sign($payload);

        return $this->call(
            'POST',
            '/webhooks/paystack',
            [], [], [],
            ['HTTP_X_PAYSTACK_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'],
            $payload,
        );
    }

    public function test_rejects_a_payload_with_an_invalid_signature(): void
    {
        Queue::fake();

        $this->postWebhook(['event' => 'charge.success', 'data' => ['reference' => 'PAY_1']], 'wrong-signature')
            ->assertStatus(401);

        Queue::assertNothingPushed();
    }

    public function test_rejects_a_missing_signature(): void
    {
        Queue::fake();

        $this->call('POST', '/webhooks/paystack', [], [], [], ['CONTENT_TYPE' => 'application/json'], '{}')
            ->assertStatus(401);

        Queue::assertNothingPushed();
    }

    public function test_a_correctly_signed_event_is_acked_and_queued_not_processed_inline(): void
    {
        Queue::fake();

        $body = ['event' => 'charge.success', 'data' => ['reference' => 'PAY_ABC']];

        $this->postWebhook($body)->assertOk();

        Queue::assertPushed(
            ProcessGatewayWebhookJob::class,
            fn (ProcessGatewayWebhookJob $job) => $job->gateway === 'paystack'
                && $job->payload['data']['reference'] === 'PAY_ABC',
        );
    }
}
