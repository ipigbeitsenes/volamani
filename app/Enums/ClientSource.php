<?php

namespace App\Enums;

enum ClientSource: string
{
    case Manual = 'manual';
    case Order = 'order';
    case Service = 'service';
    case Invoice = 'invoice';
    case Match = 'match';
    case Referral = 'referral';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Added manually',
            self::Order => 'Product order',
            self::Service => 'Service order',
            self::Invoice => 'Invoice',
            self::Match => 'Business match',
            self::Referral => 'Referral',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Manual => 'bi-pencil',
            self::Order => 'bi-box-seam',
            self::Service => 'bi-briefcase',
            self::Invoice => 'bi-receipt',
            self::Match => 'bi-diagram-3',
            self::Referral => 'bi-share',
        };
    }
}
