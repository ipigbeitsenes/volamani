<?php

namespace App\Enums;

enum MatchRequestStatus: string
{
    case Open    = 'open';
    case Matched = 'matched';   // at least one connection made
    case Closed  = 'closed';
    case Expired = 'expired';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match ($this) {
            self::Open    => 'success',
            self::Matched => 'primary',
            self::Closed  => 'secondary',
            self::Expired => 'dark',
        };
    }

    public function isOpen(): bool
    {
        return $this === self::Open || $this === self::Matched;
    }
}
