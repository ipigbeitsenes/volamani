<?php

namespace App\Services\Chat;

use App\Enums\ChatConversationStatus;
use App\Enums\ChatSenderType;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Setting;
use App\Models\User;
use App\Repositories\Chat\ChatRepository;
use App\Services\BaseService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChatService extends BaseService
{
    /** Setting keys + their sensible defaults so the widget works before anything is seeded. */
    public const DEFAULTS = [
        'chat_enabled' => '1',
        'chat_greeting' => "👋 Hi there! Need any help? We're here for you.",
        'chat_welcome' => 'Hello! Send us a message and our team will reply right here.',
        'chat_support_email' => 'support@volamani.com',
        'chat_bot_delay' => '60',
        'chat_offline_message' => "Thanks for reaching out! 🙏 All our chat agents are busy at the moment. Please email us at :email and we'll get back to you as soon as we can.",
        'chat_team_name' => 'Volamani Support',
    ];

    public function __construct(private ChatRepository $conversations) {}

    // ── Configuration ──────────────────────────────────────────────────────

    public function isEnabled(): bool
    {
        return (bool) Setting::get('chat_enabled', self::DEFAULTS['chat_enabled']);
    }

    public function setting(string $key): string
    {
        return (string) (Setting::get($key, self::DEFAULTS[$key] ?? '') ?? '');
    }

    /** Public config the browser widget needs to render itself. */
    public function widgetConfig(): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'greeting' => $this->setting('chat_greeting'),
            'welcome' => $this->setting('chat_welcome'),
            'teamName' => $this->setting('chat_team_name'),
        ];
    }

    // ── Starting / resuming a conversation ─────────────────────────────────

    public function startForUser(User $user): ChatConversation
    {
        return $this->conversations->openForUser($user->id)
            ?? ChatConversation::create(['user_id' => $user->id]);
    }

    public function startForGuest(?string $token, ?string $name = null, ?string $email = null): ChatConversation
    {
        if ($token) {
            $existing = $this->conversations->findByToken($token);
            // Only resume genuine guest conversations (never hijack a user's thread).
            if ($existing && $existing->user_id === null) {
                if (($name || $email) && ! $existing->isClosed()) {
                    $existing->fill(array_filter([
                        'guest_name' => $name,
                        'guest_email' => $email,
                    ]))->save();
                }

                return $existing;
            }
        }

        return ChatConversation::create([
            'guest_name' => $name,
            'guest_email' => $email,
        ]);
    }

    // ── Messaging ──────────────────────────────────────────────────────────

    public function postVisitorMessage(ChatConversation $conversation, string $body): ChatMessage
    {
        return DB::transaction(function () use ($conversation, $body) {
            $message = $conversation->messages()->create([
                'user_id' => $conversation->user_id,
                'sender_type' => ChatSenderType::Visitor,
                'body' => $body,
            ]);

            // A brand new message after the thread was closed re-opens it and
            // gives the offline bot a fresh chance to respond.
            if ($conversation->isClosed()) {
                $conversation->bot_replied = false;
            }

            $conversation->status = ChatConversationStatus::Open;
            $conversation->last_visitor_at = now();
            $conversation->agent_unread = $conversation->agent_unread + 1;
            $conversation->save();

            return $message;
        });
    }

    public function postAgentMessage(ChatConversation $conversation, User $agent, string $body): ChatMessage
    {
        return DB::transaction(function () use ($conversation, $agent, $body) {
            $message = $conversation->messages()->create([
                'user_id' => $agent->id,
                'sender_type' => ChatSenderType::Agent,
                'body' => $body,
            ]);

            $conversation->status = ChatConversationStatus::Pending;
            $conversation->last_agent_at = now();
            $conversation->assigned_to ??= $agent->id;
            $conversation->agent_unread = 0;
            $conversation->visitor_unread = $conversation->visitor_unread + 1;
            $conversation->save();

            return $message;
        });
    }

    /**
     * Fire the "all agents are busy, here's our email" fallback when a visitor
     * message has gone unanswered past the configured delay. Runs lazily on each
     * visitor poll (and from the chat:auto-respond command) — idempotent per round.
     */
    public function maybeBotReply(ChatConversation $conversation): ?ChatMessage
    {
        if (! $this->isEnabled() || $conversation->status !== ChatConversationStatus::Open) {
            return null;
        }
        if ($conversation->bot_replied || $conversation->last_visitor_at === null) {
            return null;
        }
        // An agent has already responded to the latest visitor message.
        if ($conversation->last_agent_at && $conversation->last_agent_at->gte($conversation->last_visitor_at)) {
            return null;
        }

        $delay = max(0, (int) $this->setting('chat_bot_delay'));
        if ($conversation->last_visitor_at->gt(now()->subSeconds($delay))) {
            return null;
        }

        $body = str_replace(':email', $this->setting('chat_support_email'), $this->setting('chat_offline_message'));

        return DB::transaction(function () use ($conversation, $body) {
            $message = $conversation->messages()->create([
                'sender_type' => ChatSenderType::Bot,
                'body' => $body,
            ]);

            $conversation->bot_replied = true;
            $conversation->visitor_unread = $conversation->visitor_unread + 1;
            $conversation->save();

            return $message;
        });
    }

    /** Sweep every eligible conversation (used by the scheduled command). */
    public function runBotSweep(): int
    {
        $delay = max(0, (int) $this->setting('chat_bot_delay'));

        return $this->conversations->dueForBotReply($delay)
            ->reduce(fn (int $count, ChatConversation $c) => $this->maybeBotReply($c) ? $count + 1 : $count, 0);
    }

    // ── Reads / fetches ────────────────────────────────────────────────────

    public function messagesSince(ChatConversation $conversation, int $afterId = 0): Collection
    {
        return $conversation->messages()->where('id', '>', $afterId)->get();
    }

    /** Visitor opened the thread: clear their unread badge + stamp team messages read. */
    public function markReadByVisitor(ChatConversation $conversation): void
    {
        $conversation->messages()
            ->whereIn('sender_type', [ChatSenderType::Agent->value, ChatSenderType::Bot->value, ChatSenderType::System->value])
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($conversation->visitor_unread !== 0) {
            $conversation->forceFill(['visitor_unread' => 0])->save();
        }
    }

    /** Agent opened the thread in the console: clear the team unread badge. */
    public function markReadByAgent(ChatConversation $conversation): void
    {
        $conversation->messages()
            ->where('sender_type', ChatSenderType::Visitor->value)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($conversation->agent_unread !== 0) {
            $conversation->forceFill(['agent_unread' => 0])->save();
        }
    }

    // ── Admin actions ──────────────────────────────────────────────────────

    public function close(ChatConversation $conversation): void
    {
        $conversation->forceFill(['status' => ChatConversationStatus::Closed])->save();
    }

    public function reopen(ChatConversation $conversation): void
    {
        $conversation->forceFill([
            'status' => ChatConversationStatus::Open,
            'bot_replied' => false,
        ])->save();
    }

    public function updateSettings(array $data): void
    {
        $types = [
            'chat_enabled' => 'boolean',
            'chat_bot_delay' => 'integer',
        ];

        foreach ($data as $key => $value) {
            if (! array_key_exists($key, self::DEFAULTS)) {
                continue;
            }
            Setting::set($key, $value, $types[$key] ?? 'string');
        }
    }

    public function currentSettings(): array
    {
        $out = [];
        foreach (array_keys(self::DEFAULTS) as $key) {
            $out[$key] = $this->setting($key);
        }

        return $out;
    }
}
