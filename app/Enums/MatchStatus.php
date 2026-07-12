<?php

namespace App\Enums;

enum MatchStatus: string
{
    case Suggested = 'suggested';   // engine-generated, no one acted yet
    case Viewed = 'viewed';      // requester opened it
    case Interested = 'interested'; // one side expressed interest
    case Connected = 'connected';   // both sides interested — contact revealed
    case Declined = 'declined';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match ($this) {
            self::Suggested => 'secondary',
            self::Viewed => 'info',
            self::Interested => 'warning',
            self::Connected => 'success',
            self::Declined => 'danger',
        };
    }
}
