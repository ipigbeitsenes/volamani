<?php

namespace App\Enums;

enum DisputeStatus: string
{
    case Open             = 'open';
    case UnderReview      = 'under_review';
    case AwaitingResponse = 'awaiting_response';
    case Resolved         = 'resolved';
    case Closed           = 'closed';
    case Escalated        = 'escalated';

    public function label(): string
    {
        return match($this) {
            self::Open             => 'Open',
            self::UnderReview      => 'Under Review',
            self::AwaitingResponse => 'Awaiting Response',
            self::Resolved         => 'Resolved',
            self::Closed           => 'Closed',
            self::Escalated        => 'Escalated',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::Open             => 'danger',
            self::UnderReview      => 'warning',
            self::AwaitingResponse => 'info',
            self::Resolved         => 'success',
            self::Closed           => 'secondary',
            self::Escalated        => 'danger',
        };
    }
}
