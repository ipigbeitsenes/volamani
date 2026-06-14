<?php

namespace App\Actions\Returns;

use App\Actions\Products\RestockOrderAction;
use App\Enums\NotificationCategory;
use App\Enums\OrderStatus;
use App\Enums\ReturnStatus;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Services\Escrow\EscrowService;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;

class ConfirmReturnAction
{
    public function __construct(
        private EscrowService       $escrow,
        private RestockOrderAction  $restock,
        private NotificationService $notifications,
    ) {}

    /**
     * Seller/admin confirms the returned item has been received → refund the
     * buyer from escrow, restock the inventory, and close the order as refunded.
     */
    public function execute(ReturnRequest $return, User $actor): ReturnRequest
    {
        abort_unless($return->canConfirmReceived(), 422, 'This return is not ready to be completed.');

        $order = $return->order;

        DB::transaction(function () use ($return, $order, $actor) {
            $escrow   = $this->escrow->forPayable($order);
            $refunded = 0;

            if ($escrow && $escrow->canRefund()) {
                $refunded = $escrow->refundableAmount();
                $this->escrow->refund($escrow, $actor, "Return {$return->reference}");
            }

            $this->restock->execute($order);

            $order->update(['status' => OrderStatus::Refunded]);

            $return->update([
                'status'          => ReturnStatus::Refunded,
                'refunded_at'     => now(),
                'refunded_amount' => $refunded,
                'decided_by'      => $actor->id,
            ]);
        });

        $this->notifications->send(
            $return->buyer,
            NotificationCategory::Payments,
            'Return refunded',
            "Your return {$return->reference} is complete — the refund has been credited to your wallet.",
            route('wallet.index'),
            'View wallet',
        );

        return $return->fresh();
    }
}
