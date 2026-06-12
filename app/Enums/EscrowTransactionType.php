<?php

namespace App\Enums;

enum EscrowTransactionType: string
{
    case Hold    = 'hold';
    case Release = 'release';
    case Refund  = 'refund';
    case Dispute = 'dispute';

    public function label(): string
    {
        return match($this) {
            self::Hold    => 'Held',
            self::Release => 'Released',
            self::Refund  => 'Refunded',
            self::Dispute => 'Disputed',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::Hold    => 'warning',
            self::Release => 'success',
            self::Refund  => 'secondary',
            self::Dispute => 'danger',
        };
    }
}
