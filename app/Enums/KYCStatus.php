<?php

namespace App\Enums;

enum KYCStatus: string
{
    case Unverified = 'unverified';
    case Pending    = 'pending';
    case Verified   = 'verified';
    case Rejected   = 'rejected';
    case Suspended  = 'suspended';

    public function label(): string
    {
        return match($this) {
            self::Unverified => 'Not Verified',
            self::Pending    => 'Pending Review',
            self::Verified   => 'Verified',
            self::Rejected   => 'Rejected',
            self::Suspended  => 'Suspended',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::Unverified => 'secondary',
            self::Pending    => 'warning',
            self::Verified   => 'success',
            self::Rejected   => 'danger',
            self::Suspended  => 'dark',
        };
    }
}
