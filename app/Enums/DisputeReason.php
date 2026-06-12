<?php

namespace App\Enums;

enum DisputeReason: string
{
    case NotDelivered   = 'not_delivered';
    case NotAsDescribed = 'not_as_described';
    case PoorQuality    = 'poor_quality';
    case LateDelivery   = 'late_delivery';
    case Unresponsive   = 'unresponsive';
    case Other          = 'other';

    public function label(): string
    {
        return match($this) {
            self::NotDelivered   => 'Item/service not delivered',
            self::NotAsDescribed => 'Not as described',
            self::PoorQuality    => 'Poor quality',
            self::LateDelivery   => 'Late delivery',
            self::Unresponsive   => 'Seller unresponsive',
            self::Other          => 'Other',
        };
    }
}
