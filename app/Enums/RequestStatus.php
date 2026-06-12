<?php

namespace App\Enums;

enum RequestStatus: string
{
    case Open      = 'open';
    case Closed    = 'closed';
    case Accepted  = 'accepted';
    case Cancelled = 'cancelled';
    case Expired   = 'expired';

    public function label(): string
    {
        return match($this) {
            self::Open      => 'Open',
            self::Closed    => 'Closed',
            self::Accepted  => 'Accepted',
            self::Cancelled => 'Cancelled',
            self::Expired   => 'Expired',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::Open      => 'success',
            self::Closed    => 'secondary',
            self::Accepted  => 'primary',
            self::Cancelled => 'danger',
            self::Expired   => 'warning text-dark',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Open;
    }
}
