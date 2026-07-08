<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ChatWidgetController extends Controller
{
    public function __construct(private ChatService $chat) {}

    /** Lightweight config poll so the widget knows whether to show + what to say. */
    public function config(): JsonResponse
    {
        return response()->json($this->chat->widgetConfig());
    }

    /** Open (or resume) a conversation for the current visitor. */
    public function start(Request $request): JsonResponse
    {
        if (! $this->chat->isEnabled()) {
            return response()->json(['enabled' => false], 403);
        }

        $data = $request->validate([
            'token' => ['nullable', 'string', 'max:40'],
            'name'  => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:120'],
        ]);

        $conversation = $request->user()
            ? $this->chat->startForUser($request->user())
            : $this->chat->startForGuest($data['token'] ?? null, $data['name'] ?? null, $data['email'] ?? null);

        $this->chat->maybeBotReply($conversation);
        $this->chat->markReadByVisitor($conversation);

        return response()->json([
            'token'    => $conversation->token,
            'status'   => $conversation->status->value,
            'messages' => $this->serialize($conversation->messages()->get()),
        ]);
    }

    /** Visitor sends a message. */
    public function message(Request $request, string $token): JsonResponse
    {
        $conversation = $this->resolve($request, $token);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $this->chat->postVisitorMessage($conversation, $data['body']);

        return response()->json(['message' => $this->serializeOne($message)], 201);
    }

    /** Poll for new messages (also lazily triggers the offline bot). */
    public function messages(Request $request, string $token): JsonResponse
    {
        $conversation = $this->resolve($request, $token);

        $this->chat->maybeBotReply($conversation);

        $after    = (int) $request->query('after', 0);
        $messages = $this->chat->messagesSince($conversation, $after);

        $this->chat->markReadByVisitor($conversation);

        return response()->json([
            'status'   => $conversation->fresh()->status->value,
            'messages' => $this->serialize($messages),
        ]);
    }

    /** Resolve by token and make sure the caller owns this thread. */
    private function resolve(Request $request, string $token): ChatConversation
    {
        $conversation = ChatConversation::where('token', $token)->firstOrFail();

        // A member-owned thread may only be touched by that signed-in member.
        if ($conversation->user_id !== null) {
            abort_unless($request->user()?->id === $conversation->user_id, 403);
        }

        return $conversation;
    }

    private function serialize(Collection $messages): array
    {
        return $messages->map(fn (ChatMessage $m) => $this->serializeOne($m))->all();
    }

    private function serializeOne(ChatMessage $message): array
    {
        return [
            'id'   => $message->id,
            'type' => $message->sender_type->value,
            'body' => $message->body,
            'time' => $message->created_at->format('g:i A'),
        ];
    }
}
