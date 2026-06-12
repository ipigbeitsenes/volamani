<?php

namespace App\Enums;

enum SubscriptionInvoiceStatus: string
{
    case Pending = 'pending';
    case Paid    = 'paid';
    case Failed  = 'failed';
    case Void    = 'void';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Paid    => 'Paid',
            self::Failed  => 'Failed',
            self::Void    => 'Void',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid    => 'success',
            self::Failed  => 'danger',
            self::Void    => 'secondary',
        };
    }
}
