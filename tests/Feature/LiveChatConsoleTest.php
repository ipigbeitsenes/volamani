<?php

namespace Tests\Feature;

use App\Models\ChatConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveChatConsoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The admin live-chat console eager-loads the latestMessage relation. It used
     * to 500 because the relation was declared HasMany while latestOfMany() returns
     * a HasOne — this reproduces that eager load at the model level.
     */
    public function test_conversations_eager_load_latest_message_without_error(): void
    {
        ChatConversation::create([
            'guest_name' => 'Visitor',
            'guest_email' => 'visitor@example.com',
            'subject' => 'Need help',
        ]);

        $conversations = ChatConversation::with(['user', 'agent', 'latestMessage'])->get();

        $this->assertCount(1, $conversations);
        $this->assertNull($conversations->first()->latestMessage); // resolves cleanly; no messages yet
    }
}
