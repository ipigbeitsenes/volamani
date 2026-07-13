<?php

namespace App\Enums;

enum PlatformCommissionStatus: string
{
    case Pending = 'pending';   // recorded, not yet resolved
    case Settled = 'settled';   // collected (e.g. debited from the seller wallet)
    case Owed = 'owed';         // due but uncollected — finance reconciles out of band
    case Waived = 'waived';     // platform takes nothing (subscription-only mode / zero fee)

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Settled => 'Settled',
            self::Owed => 'Owed',
            self::Waived => 'Waived',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Pending => 'secondary',
            self::Settled => 'success',
            self::Owed => 'warning',
            self::Waived => 'info',
        };
    }

    /** Whether this entry still represents money the platform expects to collect. */
    public function isOutstanding(): bool
    {
        return $this === self::Owed;
    }
}
