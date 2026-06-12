<?php

namespace App\Enums;

enum InteractionType: string
{
    case Note    = 'note';
    case Call    = 'call';
    case Email   = 'email';
    case Meeting = 'meeting';
    case Task    = 'task';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function icon(): string
    {
        return match ($this) {
            self::Note    => 'bi-sticky',
            self::Call    => 'bi-telephone',
            self::Email   => 'bi-envelope',
            self::Meeting => 'bi-calendar-event',
            self::Task    => 'bi-check2-square',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Note    => 'secondary',
            self::Call    => 'success',
            self::Email   => 'info',
            self::Meeting => 'primary',
            self::Task    => 'warning',
        };
    }

    public function isTask(): bool
    {
        return $this === self::Task;
    }
}
