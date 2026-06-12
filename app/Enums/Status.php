<?php

namespace App\Enums;

enum Status: string
{
    case Active    = 'active';
    case Inactive  = 'inactive';
    case Pending   = 'pending';
    case Suspended = 'suspended';
    case Banned    = 'banned';
    case Archived  = 'archived';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match($this) {
            self::Active    => 'success',
            self::Inactive  => 'secondary',
            self::Pending   => 'warning',
            self::Suspended => 'danger',
            self::Banned    => 'danger',
            self::Archived  => 'dark',
        };
    }
}
