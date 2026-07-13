<?php

namespace App\Enums;

enum PaymentGateway: string
{
    case Paystack = 'paystack';
    case Flutterwave = 'flutterwave';
    case BankTransfer = 'bank_transfer';
    case Wallet = 'wallet';

    public function label(): string
    {
        return match ($this) {
            self::Paystack => 'Paystack (Card / Bank / USSD)',
            self::Flutterwave => 'Flutterwave (Card / Bank / USSD)',
            self::BankTransfer => 'Bank Transfer',
            self::Wallet => 'Volamani Wallet',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Paystack => 'bi-credit-card',
            self::Flutterwave => 'bi-credit-card-2-front',
            self::BankTransfer => 'bi-bank',
            self::Wallet => 'bi-wallet2',
        };
    }

    /** Hosted card gateways (as opposed to wallet / manual bank transfer). */
    public function isCardGateway(): bool
    {
        return in_array($this, [self::Paystack, self::Flutterwave], true);
    }

    /** Gateways offered at checkout — card gateways only appear when configured. */
    public static function enabled(): array
    {
        return array_values(array_filter(self::cases(), fn (self $g) => match ($g) {
            self::Paystack => config('payment.paystack.secret_key') !== '',
            self::Flutterwave => config('payment.flutterwave.secret_key') !== '',
            default => true,   // bank transfer & wallet are always available
        }));
    }
}
