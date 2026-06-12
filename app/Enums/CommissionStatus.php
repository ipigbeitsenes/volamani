<?php

namespace App\Enums;

enum CommissionStatus: string
{
    case Pending   = 'pending';    // awaiting approval
    case Approved  = 'approved';   // cleared, queued for payout
    case Paid      = 'paid';       // credited to the affiliate wallet
    case Cancelled = 'cancelled';  // rejected / reversed

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Pending',
            self::Approved  => 'Approved',
            self::Paid      => 'Paid',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Pending   => 'warning',
            self::Approved  => 'info',
            self::Paid      => 'success',
            self::Cancelled => 'danger',
        };
    }
}
