<?php

namespace App\Enums;

use Illuminate\Support\Carbon;

enum BillingInterval: string
{
    case Monthly = 'monthly';
    case Yearly = 'yearly';
    case Lifetime = 'lifetime';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Monthly',
            self::Yearly => 'Yearly',
            self::Lifetime => 'Lifetime',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Monthly => '/mo',
            self::Yearly => '/yr',
            self::Lifetime => ' once',
        };
    }

    /** Advance a date by one billing cycle. Lifetime returns null (no expiry). */
    public function advance(Carbon $from): ?Carbon
    {
        return match ($this) {
            self::Monthly => $from->copy()->addMonth(),
            self::Yearly => $from->copy()->addYear(),
            self::Lifetime => null,
        };
    }

    public function isRecurring(): bool
    {
        return $this !== self::Lifetime;
    }
}
