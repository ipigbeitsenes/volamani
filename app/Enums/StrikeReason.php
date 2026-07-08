<?php

namespace App\Enums;

enum StrikeReason: string
{
    case LostDispute      = 'lost_dispute';       // dispute resolved refund-to-buyer
    case ChargebackLost   = 'chargeback_lost';    // gateway chargeback lost
    case PolicyViolation  = 'policy_violation';   // manual: policy breach
    case Manual           = 'manual';             // manual: other

    public function label(): string
    {
        return match ($this) {
            self::LostDispute     => 'Lost dispute',
            self::ChargebackLost  => 'Chargeback lost',
            self::PolicyViolation => 'Policy violation',
            self::Manual          => 'Manual strike',
        };
    }

    public function source(): string
    {
        return match ($this) {
            self::LostDispute    => 'dispute',
            self::ChargebackLost => 'chargeback',
            default              => 'manual',
        };
    }
}
