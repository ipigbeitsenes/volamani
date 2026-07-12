<?php

namespace App\Enums;

enum EscrowStatus: string
{
    case Holding = 'holding';
    case Released = 'released';
    case Refunded = 'refunded';
    case Disputed = 'disputed';
    case PartiallyReleased = 'partially_released';

    public function label(): string
    {
        return match ($this) {
            self::Holding => 'Holding',
            self::Released => 'Released',
            self::Refunded => 'Refunded',
            self::Disputed => 'Disputed',
            self::PartiallyReleased => 'Partially Released',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Holding => 'warning',
            self::Released => 'success',
            self::Refunded => 'secondary',
            self::Disputed => 'danger',
            self::PartiallyReleased => 'info',
        };
    }
}
