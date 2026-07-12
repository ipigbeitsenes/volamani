<?php

namespace App\Actions\Payment;

use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\User;
use App\Services\Payment\PaymentManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InitiatePaymentAction
{
    public function __construct(private PaymentManager $manager) {}

    public function execute(
        User $user,
        int $amountKobo,
        Model $payable,
        string $gatewayName = 'paystack',
        array $metadata = [],
        ?string $email = null
    ): array {
        return DB::transaction(function () use ($user, $amountKobo, $payable, $gatewayName, $metadata, $email) {
            $payment = Payment::create([
                'user_id' => $user->id,
                'payable_type' => get_class($payable),
                'payable_id' => $payable->getKey(),
                'gateway' => $gatewayName,
                'status' => PaymentStatus::Pending,
                'amount' => $amountKobo,
                'ip_address' => request()->ip(),
            ]);

            PaymentLog::create([
                'payment_id' => $payment->id,
                'event' => 'payment_initiated',
                'gateway' => $gatewayName,
                'payload' => ['amount' => $amountKobo, 'payable' => get_class($payable)],
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            if ($gatewayName === PaymentGateway::BankTransfer->value) {
                return ['payment' => $payment, 'redirect' => route('checkout.bank-transfer', $payment)];
            }

            $gateway = $this->manager->driver($gatewayName);
            $response = $gateway->initiate(
                $amountKobo,
                $email ?: $user->email,
                $payment->reference,
                array_merge($metadata, ['payment_id' => $payment->id])
            );

            $payment->update([
                'gateway_reference' => $response['reference'],
                'metadata' => $response,
            ]);

            PaymentLog::create([
                'payment_id' => $payment->id,
                'event' => 'gateway_initialized',
                'gateway' => $gatewayName,
                'gateway_reference' => $response['reference'],
                'payload' => $response,
                'created_at' => now(),
            ]);

            return [
                'payment' => $payment,
                'authorization_url' => $response['authorization_url'],
                'gateway_reference' => $response['reference'],
            ];
        });
    }
}
