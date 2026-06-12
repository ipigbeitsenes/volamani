<?php

namespace App\Enums;

enum KYCDocumentType: string
{
    case NIN            = 'nin';
    case BVN            = 'bvn';
    case Passport       = 'passport';
    case DriversLicense = 'drivers_license';
    case VotersCard     = 'voters_card';

    public function label(): string
    {
        return match($this) {
            self::NIN            => 'National ID (NIN)',
            self::BVN            => 'Bank Verification Number (BVN)',
            self::Passport       => 'International Passport',
            self::DriversLicense => "Driver's License",
            self::VotersCard     => "Voter's Card",
        };
    }

    /** Whether this document type has a separate back side to upload. */
    public function hasBackSide(): bool
    {
        return in_array($this, [self::DriversLicense, self::VotersCard]);
    }
}
