<?php

namespace App\Enums;

enum ConsultationSessionStatus: string
{
    case Pending   = 'pending';
    case Confirmed = 'confirmed';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow    = 'no_show';

    public function label(): string
    {
        return match($this) {
            self::Pending    => 'Awaiting Confirmation',
            self::Confirmed  => 'Confirmed',
            self::InProgress => 'In Progress',
            self::Completed  => 'Completed',
            self::Cancelled  => 'Cancelled',
            self::NoShow     => 'No Show',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::Pending    => 'warning text-dark',
            self::Confirmed  => 'primary',
            self::InProgress => 'info',
            self::Completed  => 'success',
            self::Cancelled  => 'danger',
            self::NoShow     => 'secondary',
        };
    }
}
