<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Vendor = 'vendor';
    case Buyer = 'buyer';
    case Consultant = 'consultant';
    case Moderator = 'moderator';

    public function label(): string
    {
        return match($this) {
            self::Admin      => 'Administrator',
            self::Vendor     => 'Vendor',
            self::Buyer      => 'Buyer',
            self::Consultant => 'Consultant',
            self::Moderator  => 'Moderator',
        };
    }
}
