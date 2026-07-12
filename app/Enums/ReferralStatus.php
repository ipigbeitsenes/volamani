<?php

namespace App\Enums;

enum ReferralStatus: string
{
    case Pending = 'pending';    // signed up, no qualifying purchase yet
    case Qualified = 'qualified';  // made a qualifying purchase
    case Rewarded = 'rewarded';   // commission generated for the referrer
    case Expired = 'expired';    // attribution window lapsed

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Qualified => 'Qualified',
            self::Rewarded => 'Rewarded',
            self::Expired => 'Expired',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Pending => 'secondary',
            self::Qualified => 'info',
            self::Rewarded => 'success',
            self::Expired => 'light',
        };
    }
}
