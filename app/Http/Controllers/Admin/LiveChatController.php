<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ChatConversationStatus;
use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Repositories\Chat\ChatRepository;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiveChatController extends Controller
{
    public function __construct(
        private ChatService $chat,
        private ChatRepository $conversations,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only('status', 'search');

        return view('admin.live-chat.index', [
            'conversations' => $this->conversations->forAdmin($filters),
            'filters' => $filters,
            'statuses' => ChatConversationStatus::cases(),
            'openCount' => $this->conversations->unansweredCount(),
        ]);
    }

    public function show(ChatConversation $conversation): View
    {
        $conversation->load(['user', 'agent', 'messages.sender']);
        $this->chat->markReadByAgent($conversation);

        return view('admin.live-chat.show', [
            'conversation' => $conversation,
            'messages' => $conversation->messages,
        ]);
    }

    /** Live refresh for the open console thread. */
    public function poll(Request $request, ChatConversation $conversation): JsonResponse
    {
        $after = (int) $request->query('after', 0);
        $messages = $this->chat->messagesSince($conversation, $after);
        $this->chat->markReadByAgent($conversation);

        return response()->json([
            'status' => $conversation->fresh()->status->value,
            'messages' => $messages->map(fn (ChatMessage $m) => $this->serialize($m))->all(),
        ]);
    }

    public function reply(Request $request, ChatConversation $conversation): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $this->chat->postAgentMessage($conversation, $request->user(), $data['body']);

        if ($request->wantsJson()) {
            return response()->json(['message' => $this->serialize($message)], 201);
        }

        return back();
    }

    public function close(ChatConversation $conversation): RedirectResponse
    {
        $this->chat->close($conversation);
        $this->flashSuccess('Conversation closed.');

        return back();
    }

    public function reopen(ChatConversation $conversation): RedirectResponse
    {
        $this->chat->reopen($conversation);
        $this->flashSuccess('Conversation reopened.');

        return back();
    }

    public function settings(): View
    {
        return view('admin.live-chat.settings', [
            'settings' => $this->chat->currentSettings(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'chat_enabled' => ['nullable', 'boolean'],
            'chat_greeting' => ['required', 'string', 'max:255'],
            'chat_welcome' => ['required', 'string', 'max:500'],
            'chat_support_email' => ['required', 'email', 'max:120'],
            'chat_bot_delay' => ['required', 'integer', 'min:0', 'max:3600'],
            'chat_offline_message' => ['required', 'string', 'max:500'],
            'chat_team_name' => ['required', 'string', 'max:80'],
        ]);

        $data['chat_enabled'] = $request->boolean('chat_enabled') ? '1' : '0';

        $this->chat->updateSettings($data);
        $this->flashSuccess('Live chat settings saved.');

        return back();
    }

    private function serialize(ChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'type' => $message->sender_type->value,
            'body' => $message->body,
            'name' => $message->sender?->name,
            'time' => $message->created_at->format('g:i A'),
        ];
    }
}
