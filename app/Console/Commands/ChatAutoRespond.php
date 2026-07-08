<?php

namespace App\Console\Commands;

use App\Services\Chat\ChatService;
use Illuminate\Console\Command;

class ChatAutoRespond extends Command
{
    protected $signature = 'chat:auto-respond';

    protected $description = 'Send the offline "all agents are busy" bot reply to unanswered live-chat conversations';

    public function handle(ChatService $chat): int
    {
        $sent = $chat->runBotSweep();

        $this->info("Sent {$sent} offline auto-reply/replies.");

        return self::SUCCESS;
    }
}
