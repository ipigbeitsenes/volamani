<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Pending = 'pending';    // created, awaiting first gateway payment
    case Trialing = 'trialing';
    case Active = 'active';
    case PastDue = 'past_due';   // renewal charge failed, in grace
    case Cancelled = 'cancelled';  // auto-renew off, still within period
    case Expired = 'expired';    // access ended

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Payment',
            self::Trialing => 'Trialing',
            self::Active => 'Active',
            self::PastDue => 'Past Due',
            self::Cancelled => 'Cancelled',
            self::Expired => 'Expired',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Pending => 'secondary',
            self::Trialing => 'info',
            self::Active => 'success',
            self::PastDue => 'warning',
            self::Cancelled => 'secondary',
            self::Expired => 'danger',
        };
    }

    /** Statuses that still grant plan access. */
    public function grantsAccess(): bool
    {
        return in_array($this, [self::Trialing, self::Active, self::PastDue, self::Cancelled]);
    }
}
