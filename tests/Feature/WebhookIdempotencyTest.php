<?php

namespace Tests\Feature;

use App\Actions\Payment\HandleWebhookAction;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Paystack delivers the same webhook more than once (their own retries, plus the
 * browser callback verifying in parallel). Processing must be idempotent: a
 * payment is verified — and therefore fulfilled — exactly once, no matter how
 * many times the event arrives.
 */
class WebhookIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    private function pendingPayment(): Payment
    {
        $user = User::factory()->create();

        // No payable ⇒ fulfilment is a safe no-op; this test isolates the
        // verify/dedup guard from the (separately tested) fulfilment side effects.
        return Payment::create([
            'user_id' => $user->id,
            'gateway' => PaymentGateway::Paystack,
            'gateway_reference' => 'PSK_REF_123',
            'status' => PaymentStatus::Pending,
            'currency' => 'NGN',
            'amount' => 500_000,
        ]);
    }

    private function fakeGatewaySuccess(string $reference): void
    {
        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'amount' => 500_000,
                    'reference' => $reference,
                    'paid_at' => '2026-07-11T10:00:00Z',
                ],
            ]),
        ]);
    }

    public function test_duplicate_charge_success_verifies_the_payment_only_once(): void
    {
        $payment = $this->pendingPayment();
        $this->fakeGatewaySuccess('PSK_REF_123');

        $body = ['event' => 'charge.success', 'data' => ['reference' => 'PSK_REF_123']];
        $handler = app(HandleWebhookAction::class);

        $handler->execute($body, 'paystack');
        $handler->execute($body, 'paystack'); // duplicate delivery

        // The gateway was consulted exactly once — no double verification.
        Http::assertSentCount(1);

        $payment->refresh();
        $this->assertSame(PaymentStatus::Success, $payment->status);
        $this->assertNotNull($payment->paid_at);

        // Exactly one "verified" ledger entry survives the duplicate.
        $this->assertSame(1, PaymentLog::where('gateway_reference', 'PSK_REF_123')
            ->where('event', 'payment_verified')
            ->count());
    }

    public function test_already_successful_payment_is_never_re_verified(): void
    {
        $payment = $this->pendingPayment();
        $payment->update(['status' => PaymentStatus::Success, 'paid_at' => now()]);
        $this->fakeGatewaySuccess('PSK_REF_123');

        app(HandleWebhookAction::class)->execute(
            ['event' => 'charge.success', 'data' => ['reference' => 'PSK_REF_123']],
            'paystack',
        );

        // isSuccessful() short-circuits before any gateway round-trip.
        Http::assertNothingSent();
    }
}
