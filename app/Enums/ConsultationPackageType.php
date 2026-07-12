<?php

namespace App\Enums;

enum ConsultationPackageType: string
{
    case OneTime = 'one_time';
    case Retainer = 'retainer';

    public function label(): string
    {
        return match ($this) {
            self::OneTime => 'One-Time Session',
            self::Retainer => 'Monthly Retainer',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::OneTime => 'primary',
            self::Retainer => 'success',
        };
    }
}
