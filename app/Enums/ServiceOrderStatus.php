<?php

namespace App\Enums;

enum ServiceOrderStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case InProgress = 'in_progress';
    case Delivered = 'delivered';
    case RevisionRequested = 'revision_requested';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Disputed = 'disputed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Payment',
            self::Active => 'Awaiting Requirements',
            self::InProgress => 'In Progress',
            self::Delivered => 'Delivered',
            self::RevisionRequested => 'Revision Requested',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::Disputed => 'Disputed',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Pending => 'secondary',
            self::Active => 'info',
            self::InProgress => 'primary',
            self::Delivered => 'warning',
            self::RevisionRequested => 'warning',
            self::Completed => 'success',
            self::Cancelled => 'danger',
            self::Disputed => 'danger',
        };
    }
}
