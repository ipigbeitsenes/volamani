<?php

namespace App\Enums;

enum ChargebackStatus: string
{
    case Open      = 'open';        // just received; funds frozen/clawed, awaiting outcome
    case Contested = 'contested';   // vendor/admin submitted evidence to the gateway
    case Won       = 'won';         // resolved in the merchant's favour → funds restored
    case Lost      = 'lost';        // resolved in the buyer's favour → clawback stands, strike issued

    public function label(): string
    {
        return match ($this) {
            self::Open      => 'Open',
            self::Contested => 'Contested',
            self::Won       => 'Won',
            self::Lost      => 'Lost',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Open      => 'warning',
            self::Contested => 'info',
            self::Won       => 'success',
            self::Lost      => 'danger',
        };
    }

    /** Still awaiting a gateway outcome. */
    public function isOpen(): bool
    {
        return in_array($this, [self::Open, self::Contested], true);
    }
}
