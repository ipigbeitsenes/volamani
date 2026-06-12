<?php

namespace App\Enums;

enum ClientStatus: string
{
    case Lead     = 'lead';
    case Active   = 'active';
    case Inactive = 'inactive';
    case Archived = 'archived';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match ($this) {
            self::Lead     => 'info',
            self::Active   => 'success',
            self::Inactive => 'secondary',
            self::Archived => 'dark',
        };
    }
}