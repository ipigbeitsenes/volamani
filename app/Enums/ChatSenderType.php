<?php

namespace App\Enums;

enum ChatSenderType: string
{
    case Visitor = 'visitor';
    case Agent = 'agent';
    case Bot = 'bot';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::Visitor => 'Visitor',
            self::Agent => 'Agent',
            self::Bot => 'Assistant',
            self::System => 'System',
        };
    }
}
