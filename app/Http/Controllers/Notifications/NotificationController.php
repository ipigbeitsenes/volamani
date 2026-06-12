<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notifications\UpdateNotificationPreferencesRequest;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function index(Request $request): View
    {
        $filter        = $request->query('filter') === 'unread' ? 'unread' : null;
        $notifications = $this->notifications->forUser($request->user(), 20, $filter);
        $unread        = $this->notifications->unreadCount($request->user());

        return view('marketplace.notifications.index', compact('notifications', 'unread', 'filter'));
    }

    /** JSON feed for the navbar bell dropdown (polled / loaded on demand). */
    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'unread' => $this->notifications->unreadCount($user),
            'items'  => $this->notifications->recent($user)->map(fn ($n) => [
                'id'      => $n->id,
                'title'   => $n->data['title'] ?? 'Notification',
                'message' => $n->data['message'] ?? '',
                'icon'    => $n->data['icon'] ?? 'bi-bell',
                'url'     => route('notifications.open', $n->id),
                'read'    => $n->read_at !== null,
                'time'    => $n->created_at->diffForHumans(),
            ]),
        ]);
    }

    /** Mark a notification read, then bounce to its target URL. */
    public function open(Request $request, string $id): RedirectResponse
    {
        $user         = $request->user();
        $notification = $this->notifications->find($user, $id);

        $notification?->markAsRead();

        $target = $notification?->data['url'] ?? null;

        return redirect($target ?? route('notifications.index'));
    }

    public function markRead(Request $request, string $id): RedirectResponse
    {
        $this->notifications->markAsRead($request->user(), $id);

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $this->notifications->markAllAsRead($request->user());
        $this->flashSuccess('All notifications marked as read.');

        return back();
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $this->notifications->delete($request->user(), $id);

        return back();
    }

    public function clearAll(Request $request): RedirectResponse
    {
        $this->notifications->clearAll($request->user());
        $this->flashSuccess('Notifications cleared.');

        return redirect()->route('notifications.index');
    }

    public function preferences(Request $request): View
    {
        $matrix = $this->notifications->preferenceMatrix($request->user());

        return view('marketplace.notifications.preferences', compact('matrix'));
    }

    public function updatePreferences(UpdateNotificationPreferencesRequest $request): RedirectResponse
    {
        $this->notifications->updatePreferences($request->user(), $request->preferences());
        $this->flashSuccess('Notification preferences saved.');

        return redirect()->route('notifications.preferences');
    }
}
