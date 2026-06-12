<?php

namespace App\Enums;

enum DisputeResolution: string
{
    case ReleaseToVendor = 'release_to_vendor';
    case RefundToBuyer   = 'refund_to_buyer';
    case Split           = 'split';
    case Dismissed       = 'dismissed';

    public function label(): string
    {
        return match($this) {
            self::ReleaseToVendor => 'Released to Vendor',
            self::RefundToBuyer   => 'Refunded to Buyer',
            self::Split           => 'Split Settlement',
            self::Dismissed       => 'Dismissed',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::ReleaseToVendor => 'success',
            self::RefundToBuyer   => 'info',
            self::Split           => 'warning',
            self::Dismissed       => 'secondary',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::ReleaseToVendor => 'Funds released in full to the vendor.',
            self::RefundToBuyer   => "Funds refunded in full to the buyer's wallet.",
            self::Split           => 'Funds split — part released to the vendor, the rest refunded to the buyer.',
            self::Dismissed       => 'Dispute dismissed; held funds released to the vendor.',
        };
    }
}
