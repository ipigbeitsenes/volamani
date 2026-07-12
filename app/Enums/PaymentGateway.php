<?php

namespace App\Enums;

enum PaymentGateway: string
{
    case Paystack = 'paystack';
    case BankTransfer = 'bank_transfer';
    case Wallet = 'wallet';

    public function label(): string
    {
        return match ($this) {
            self::Paystack => 'Paystack (Card / Bank / USSD)',
            self::BankTransfer => 'Bank Transfer',
            self::Wallet => 'Volamani Wallet',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Paystack => 'bi-credit-card',
            self::BankTransfer => 'bi-bank',
            self::Wallet => 'bi-wallet2',
        };
    }
}
