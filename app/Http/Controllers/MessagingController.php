<?php

namespace App\Http\Controllers;

use App\Enums\NotificationCategory;
use App\Models\Product;
use App\Models\SellerConversation;
use App\Models\User;
use App\Models\Vendor;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MessagingController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    /** Unified inbox — threads where the user is the buyer OR the vendor owner. */
    public function index(Request $request): View
    {
        $user = $request->user();

        $conversations = SellerConversation::forUser($user)
            ->with(['buyer', 'vendor', 'product', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return view('messages.index', compact('conversations', 'user'));
    }

    /** Buyer starts a message about a product (compose screen). */
    public function compose(Request $request): View|RedirectResponse
    {
        $product = Product::with('vendor')->findOrFail($request->integer('product'));
        $vendor = $product->vendor;

        if (! $vendor instanceof Vendor) {
            abort(404);
        }

        if ($vendor->user_id === $request->user()->id) {
            return redirect()->route('messages.index')->with('info', 'That listing is your own store.');
        }

        return view('messages.compose', ['product' => $product, 'vendor' => $vendor]);
    }

    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $product = Product::with('vendor')->findOrFail($data['product_id']);
        $vendor = $product->vendor;

        if (! $vendor instanceof Vendor) {
            abort(404);
        }

        abort_if($vendor->user_id === $request->user()->id, 422, "You can't message your own store.");

        $conversation = SellerConversation::firstOrCreate(
            ['buyer_id' => $request->user()->id, 'vendor_id' => $vendor->id],
            ['product_id' => $product->id],
        );

        $this->postMessage($conversation, $request->user(), $data['body']);

        return redirect()->route('messages.show', $conversation)->with('success', 'Message sent.');
    }

    public function show(SellerConversation $conversation, Request $request): View
    {
        $user = $request->user();
        $conversation->load(['vendor', 'buyer', 'product']);
        abort_unless($conversation->includes($user), 403);

        // Mark the other party's messages as read.
        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $user->id)
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()->with('sender')->get();

        return view('messages.show', compact('conversation', 'messages', 'user'));
    }

    public function reply(SellerConversation $conversation, Request $request): RedirectResponse
    {
        $user = $request->user();
        $conversation->load('vendor');
        abort_unless($conversation->includes($user), 403);

        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $this->postMessage($conversation, $user, $data['body']);

        return redirect()->route('messages.show', $conversation);
    }

    /** Persist a message, bump the thread, and notify the other participant. */
    private function postMessage(SellerConversation $conversation, User $sender, string $body): void
    {
        $conversation->messages()->create(['sender_id' => $sender->id, 'body' => $body]);
        $conversation->update(['last_message_at' => now()]);

        $recipient = $this->recipientFor($conversation, $sender);

        if ($recipient) {
            $this->notifications->send(
                $recipient,
                NotificationCategory::Messages,
                'New message from '.$sender->name,
                Str::limit($body, 100),
                route('messages.show', $conversation),
                'View message',
            );
        }
    }

    /** The participant who should be notified of a message from $sender. */
    private function recipientFor(SellerConversation $conversation, User $sender): ?User
    {
        if ($conversation->isVendorSide($sender)) {
            $buyer = $conversation->buyer;

            return $buyer instanceof User ? $buyer : null;
        }

        $vendor = $conversation->vendor;
        $owner = $vendor instanceof Vendor ? $vendor->user : null;

        return $owner instanceof User ? $owner : null;
    }
}
