<?php

namespace App\Enums;

enum ChatConversationStatus: string
{
    case Open = 'open';    // awaiting / in conversation with the team
    case Pending = 'pending'; // agent replied, waiting on the visitor
    case Closed = 'closed';  // resolved / archived

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Pending => 'Pending',
            self::Closed => 'Closed',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Open => 'danger',
            self::Pending => 'warning',
            self::Closed => 'secondary',
        };
    }
}
