<?php

namespace App\Repositories\Chat;

use App\Enums\ChatConversationStatus;
use App\Models\ChatConversation;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ChatRepository extends BaseRepository
{
    public function __construct(ChatConversation $model)
    {
        parent::__construct($model);
    }

    public function findByToken(string $token): ?ChatConversation
    {
        return ChatConversation::where('token', $token)->first();
    }

    /** Latest still-open conversation belonging to a signed-in visitor. */
    public function openForUser(int $userId): ?ChatConversation
    {
        return ChatConversation::open()
            ->where('user_id', $userId)
            ->latest('id')
            ->first();
    }

    /** Admin queue, newest activity first, with optional status / keyword filters. */
    public function forAdmin(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return ChatConversation::query()
            ->with(['user', 'agent', 'latestMessage'])
            ->withCount('messages')
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('guest_name', 'like', "%{$search}%")
                        ->orWhere('guest_email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->orderByRaw('GREATEST(COALESCE(last_visitor_at, created_at), COALESCE(last_agent_at, created_at)) DESC')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function openCount(): int
    {
        return ChatConversation::open()->count();
    }

    /** Open conversations with an unanswered visitor message the team hasn't opened. */
    public function unansweredCount(): int
    {
        return ChatConversation::open()->where('agent_unread', '>', 0)->count();
    }

    /**
     * Open conversations whose last visitor message is older than $threshold seconds
     * with no agent reply since and no bot fallback yet — candidates for the offline bot.
     */
    public function dueForBotReply(int $thresholdSeconds): \Illuminate\Support\Collection
    {
        return ChatConversation::query()
            ->where('status', ChatConversationStatus::Open->value)
            ->where('bot_replied', false)
            ->whereNotNull('last_visitor_at')
            ->where('last_visitor_at', '<=', now()->subSeconds($thresholdSeconds))
            ->where(function ($q) {
                $q->whereNull('last_agent_at')
                    ->orWhereColumn('last_agent_at', '<', 'last_visitor_at');
            })
            ->get();
    }
}
