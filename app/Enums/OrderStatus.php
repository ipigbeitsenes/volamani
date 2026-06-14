<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending    = 'pending';
    case Paid       = 'paid';
    case Processing = 'processing';
    case InProgress = 'in_progress';
    case Shipped    = 'shipped';
    case Delivered  = 'delivered';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';
    case Refunded   = 'refunded';
    case Disputed   = 'disputed';

    public function label(): string
    {
        return match($this) {
            self::Pending    => 'Pending',
            self::Paid       => 'Paid',
            self::Processing => 'Processing',
            self::InProgress => 'In Progress',
            self::Shipped    => 'Shipped',
            self::Delivered  => 'Delivered',
            self::Completed  => 'Completed',
            self::Cancelled  => 'Cancelled',
            self::Refunded   => 'Refunded',
            self::Disputed   => 'Disputed',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::Pending    => 'warning',
            self::Paid       => 'info',
            self::Processing => 'primary',
            self::InProgress => 'primary',
            self::Shipped    => 'info',
            self::Delivered  => 'success',
            self::Completed  => 'success',
            self::Cancelled  => 'danger',
            self::Refunded   => 'secondary',
            self::Disputed   => 'danger',
        };
    }
}
