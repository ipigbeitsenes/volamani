<?php

namespace App\Actions\Returns;

use App\Enums\NotificationCategory;
use App\Enums\ReturnStatus;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Services\Escrow\EscrowService;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class RequestReturnAction
{
    public function __construct(
        private EscrowService $escrow,
        private NotificationService $notifications,
    ) {}

    /**
     * Buyer opens a return on a delivered physical order. Verifies the funds are
     * still held, stores any evidence photos, and FREEZES the escrow auto-release
     * so the money can't slip out to the vendor while the return is in flight.
     */
    public function execute(Order $order, User $buyer, array $data): ReturnRequest
    {
        $escrow = $this->escrow->forPayable($order);
        abort_unless($escrow && $escrow->canRefund(), 422, 'This order can no longer be returned — the payment has already settled.');
        abort_unless($order->canRequestReturn(), 422, 'This order is not eligible for a return.');

        return DB::transaction(function () use ($order, $buyer, $data, $escrow) {
            $photos = [];
            foreach (($data['photos'] ?? []) as $file) {
                if ($file instanceof UploadedFile) {
                    $photos[] = $file->store('returns/'.$order->id, 'public');
                }
            }

            $return = ReturnRequest::create([
                'order_id' => $order->id,
                'buyer_id' => $buyer->id,
                'vendor_id' => $order->vendor_id,
                'reason' => $data['reason'],
                'description' => $data['description'] ?? null,
                'photos' => $photos ?: null,
                'status' => ReturnStatus::Requested,
                'requested_at' => now(),
            ]);

            // Freeze the funds: no auto-release while a return is open.
            $escrow->update(['auto_release_at' => null]);

            $this->notifySeller($return, $order);
            $this->notifyAdmins($return, $order);

            return $return;
        });
    }

    private function notifySeller(ReturnRequest $return, Order $order): void
    {
        if ($order->vendor?->user) {
            $this->notifications->send(
                $order->vendor->user,
                NotificationCategory::Orders,
                'Return requested',
                "A buyer requested a return on order {$order->reference}. Review and respond.",
                route('vendor.returns.index'),
                'Review return',
            );
        }
    }

    private function notifyAdmins(ReturnRequest $return, Order $order): void
    {
        foreach (User::role('admin')->get() as $admin) {
            $this->notifications->send(
                $admin,
                NotificationCategory::Escrow,
                'Return opened',
                "Return {$return->reference} opened on order {$order->reference}.",
                route('admin.returns.index'),
                'View returns',
            );
        }
    }
}
