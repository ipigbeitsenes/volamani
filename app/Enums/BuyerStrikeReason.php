<?php

namespace App\Enums;

/**
 * Why a buyer accrued an abuse strike. The buyer-side mirror of StrikeReason:
 * a signal that a buyer may be gaming buyer-protection (serial "fake buyer").
 */
enum BuyerStrikeReason: string
{
    case LostDispute          = 'lost_dispute';           // a dispute the buyer raised was resolved for the seller
    case FraudulentChargeback = 'fraudulent_chargeback';  // a chargeback the buyer filed was won by the merchant
    case PolicyViolation      = 'policy_violation';       // manual: abuse / policy breach
    case Manual               = 'manual';                 // manual: other

    public function label(): string
    {
        return match ($this) {
            self::LostDispute          => 'Dispute rejected',
            self::FraudulentChargeback => 'Fraudulent chargeback',
            self::PolicyViolation      => 'Policy violation',
            self::Manual               => 'Manual strike',
        };
    }

    public function source(): string
    {
        return match ($this) {
            self::LostDispute          => 'dispute',
            self::FraudulentChargeback => 'chargeback',
            default                    => 'manual',
        };
    }
}
