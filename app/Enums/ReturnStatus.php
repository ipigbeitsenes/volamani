<?php

namespace App\Enums;

enum ReturnStatus: string
{
    case Requested    = 'requested';     // buyer opened it; awaiting seller decision
    case Approved     = 'approved';      // seller/admin approved; buyer to ship item back
    case ShippedBack  = 'shipped_back';  // buyer shipped the item back
    case Refunded     = 'refunded';      // seller/admin confirmed receipt → refunded
    case Rejected     = 'rejected';      // seller/admin declined the return
    case Cancelled    = 'cancelled';     // buyer withdrew the request

    public function label(): string
    {
        return match ($this) {
            self::Requested   => 'Requested',
            self::Approved    => 'Approved — ship it back',
            self::ShippedBack => 'Shipped back',
            self::Refunded    => 'Refunded',
            self::Rejected    => 'Rejected',
            self::Cancelled   => 'Cancelled',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Requested   => 'warning',
            self::Approved    => 'info',
            self::ShippedBack => 'primary',
            self::Refunded    => 'success',
            self::Rejected    => 'danger',
            self::Cancelled   => 'secondary',
        };
    }

    /** Still in flight — counts as an "active" return that blocks a duplicate. */
    public function isActive(): bool
    {
        return in_array($this, [self::Requested, self::Approved, self::ShippedBack], true);
    }
}
