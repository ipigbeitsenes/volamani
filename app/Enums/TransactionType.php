<?php

namespace App\Enums;

enum TransactionType: string
{
    case Credit          = 'credit';
    case Debit           = 'debit';
    case EscrowHold      = 'escrow_hold';
    case EscrowRelease   = 'escrow_release';
    case EscrowRefund    = 'escrow_refund';
    case Commission      = 'commission';
    case Withdrawal      = 'withdrawal';
    case Refund          = 'refund';
    case Bonus           = 'bonus';
    case AffiliateEarning = 'affiliate_earning';
    case WalletFunding   = 'wallet_funding';

    public function label(): string
    {
        return match($this) {
            self::Credit           => 'Credit',
            self::Debit            => 'Debit',
            self::EscrowHold       => 'Escrow Hold',
            self::EscrowRelease    => 'Escrow Release',
            self::EscrowRefund     => 'Escrow Refund',
            self::Commission       => 'Commission',
            self::Withdrawal       => 'Withdrawal',
            self::Refund           => 'Refund',
            self::Bonus            => 'Bonus',
            self::AffiliateEarning => 'Affiliate Earning',
            self::WalletFunding    => 'Wallet Funding',
        };
    }

    public function isCredit(): bool
    {
        return in_array($this, [
            self::Credit,
            self::EscrowRelease,
            self::Refund,
            self::Bonus,
            self::AffiliateEarning,
            self::WalletFunding,
        ]);
    }
}
