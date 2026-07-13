<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Gateways\FlutterwaveGateway;
use App\Gateways\PaystackGateway;

class PaymentManager
{
    public function driver(?string $driver = null): PaymentGatewayInterface
    {
        $driver = $driver ?? config('payment.default', 'paystack');

        return match ($driver) {
            'paystack' => app(PaystackGateway::class),
            'flutterwave' => app(FlutterwaveGateway::class),
            default => throw new \InvalidArgumentException("Unsupported payment gateway: [{$driver}]"),
        };
    }

    public function paystack(): PaymentGatewayInterface
    {
        return $this->driver('paystack');
    }

    public function flutterwave(): PaymentGatewayInterface
    {
        return $this->driver('flutterwave');
    }
}
