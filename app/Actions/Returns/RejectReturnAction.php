<?php

namespace App\Actions\Returns;

use App\Enums\EscrowStatus;
use App\Enums\NotificationCategory;
use App\Enums\ReturnStatus;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Services\Escrow\EscrowService;
use App\Services\Notifications\NotificationService;
use App\Support\BusinessDayCalculator;

class RejectReturnAction
{
    public function __construct(
        private EscrowService $escrow,
        private NotificationService $notifications,
    ) {}

    public function execute(ReturnRequest $return, User $actor, string $note): ReturnRequest
    {
        abort_unless($return->canReject(), 422, 'This return cannot be rejected at its current stage.');

        $return->update([
            'status' => ReturnStatus::Rejected,
            'rejected_at' => now(),
            'decided_by' => $actor->id,
            'decision_note' => $note,
        ]);

        $this->rearmEscrow($return);

        $this->notifications->send(
            $return->buyer,
            NotificationCategory::Orders,
            'Return declined',
            "Your return {$return->reference} was declined: {$note}. If you disagree, you can open a dispute.",
            route('orders.show', $return->order_id),
            'View order',
        );

        return $return->fresh();
    }

    /** Re-arm the escrow auto-release that the return request had frozen. */
    private function rearmEscrow(ReturnRequest $return): void
    {
        $escrow = $this->escrow->forPayable($return->order);
        if ($escrow && $escrow->status === EscrowStatus::Holding && $escrow->auto_release_at === null) {
            $days = (int) config('business_days.release_days', 3);
            $escrow->update([
                'auto_release_at' => app(BusinessDayCalculator::class)->addBusinessDays(now(), max(1, $days)),
            ]);
        }
    }
}
