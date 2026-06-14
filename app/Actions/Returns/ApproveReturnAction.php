<?php

namespace App\Actions\Returns;

use App\Enums\NotificationCategory;
use App\Enums\ReturnStatus;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Services\Notifications\NotificationService;

class ApproveReturnAction
{
    public function __construct(private NotificationService $notifications) {}

    public function execute(ReturnRequest $return, User $actor, ?string $note = null): ReturnRequest
    {
        abort_unless($return->canApprove(), 422, 'This return cannot be approved at its current stage.');

        $return->update([
            'status'        => ReturnStatus::Approved,
            'approved_at'   => now(),
            'decided_by'    => $actor->id,
            'decision_note' => $note,
        ]);

        $this->notifications->send(
            $return->buyer,
            NotificationCategory::Orders,
            'Return approved',
            "Your return {$return->reference} was approved. Ship the item back and add the tracking number.",
            route('orders.show', $return->order_id),
            'View order',
        );

        return $return->fresh();
    }
}
