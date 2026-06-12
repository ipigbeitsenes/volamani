<?php

namespace App\Enums;

enum UserType: string
{
    case Individual = 'individual';
    case Business   = 'business';
    case Agency     = 'agency';
    case Freelancer = 'freelancer';

    public function label(): string
    {
        return match($this) {
            self::Individual => 'Individual',
            self::Business   => 'Business',
            self::Agency     => 'Agency',
            self::Freelancer => 'Freelancer',
        };
    }
}
