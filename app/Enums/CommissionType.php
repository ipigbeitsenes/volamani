<?php

namespace App\Enums;

enum CommissionType: string
{
    case SignupBonus = 'signup_bonus';     // flat reward when a referred user registers
    case SaleCommission = 'sale_commission';  // % of a referred user's purchase

    public function label(): string
    {
        return match ($this) {
            self::SignupBonus => 'Signup Bonus',
            self::SaleCommission => 'Sale Commission',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::SignupBonus => 'bi-person-plus',
            self::SaleCommission => 'bi-cart-check',
        };
    }
}
