<?php

namespace App\Enums;

enum WithdrawalStatus: string
{
    case Pending    = 'pending';
    case Processing = 'processing';
    case Approved   = 'approved';
    case Paid       = 'paid';
    case Rejected   = 'rejected';
    case Failed     = 'failed';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match($this) {
            self::Pending    => 'warning',
            self::Processing => 'info',
            self::Approved   => 'primary',
            self::Paid       => 'success',
            self::Rejected   => 'danger',
            self::Failed     => 'danger',
        };
    }
}
