<?php

namespace App\Enums;

enum ReturnReason: string
{
    case NotAsDescribed = 'not_as_described';
    case Damaged        = 'damaged';
    case WrongItem      = 'wrong_item';
    case MissingParts   = 'missing_parts';
    case Defective      = 'defective';
    case Other          = 'other';

    public function label(): string
    {
        return match ($this) {
            self::NotAsDescribed => 'Not as described',
            self::Damaged        => 'Arrived damaged',
            self::WrongItem      => 'Wrong item sent',
            self::MissingParts   => 'Missing parts / items',
            self::Defective      => 'Defective / not working',
            self::Other          => 'Other',
        };
    }

    /** Reasons where the seller is at fault — seller pays return shipping. */
    public function isSellerFault(): bool
    {
        return $this !== self::Other;
    }
}
