<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Draft    = 'draft';
    case Pending  = 'pending';
    case Active   = 'active';
    case Rejected = 'rejected';
    case Archived = 'archived';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match($this) {
            self::Draft    => 'secondary',
            self::Pending  => 'warning',
            self::Active   => 'success',
            self::Rejected => 'danger',
            self::Archived => 'dark',
        };
    }

    public function isPublic(): bool
    {
        return $this === self::Active;
    }
}
