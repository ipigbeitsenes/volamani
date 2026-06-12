<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case Pending   = 'pending';
    case Accepted  = 'accepted';
    case Rejected  = 'rejected';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Pending Review',
            self::Accepted  => 'Accepted',
            self::Rejected  => 'Not Selected',
            self::Withdrawn => 'Withdrawn',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::Pending   => 'warning text-dark',
            self::Accepted  => 'success',
            self::Rejected  => 'secondary',
            self::Withdrawn => 'danger',
        };
    }
}
