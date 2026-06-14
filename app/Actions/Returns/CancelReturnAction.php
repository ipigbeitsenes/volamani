<?php

namespace App\Actions\Returns;

use App\Enums\EscrowStatus;
use App\Enums\NotificationCategory;
use App\Enums\ReturnStatus;
use App\Models\ReturnRequest;
use App\Services\Escrow\EscrowService;
use App\Services\Notifications\NotificationService;
use App\Support\BusinessDayCalculator;

class CancelReturnAction
{
    public function __construct(
        private EscrowService       $escrow,
        private NotificationService $notifications,
    ) {}

    /** Buyer withdraws their return before it's completed → re-arm the escrow. */
    public function execute(ReturnRequest $return): ReturnRequest
    {
        abort_unless($return->canCancel(), 422, 'This return can no longer be cancelled.');

        $return->update(['status' => ReturnStatus::Cancelled]);

        $escrow = $this->escrow->forPayable($return->order);
        if ($escrow && $escrow->status === EscrowStatus::Holding && $escrow->auto_release_at === null) {
            $days = (int) config('business_days.release_days', 3);
            $escrow->update([
                'auto_release_at' => app(BusinessDayCalculator::class)->addBusinessDays(now(), max(1, $days)),
            ]);
        }

        if ($return->vendor?->user) {
            $this->notifications->send(
                $return->vendor->user,
                NotificationCategory::Orders,
                'Return cancelled',
                "The buyer cancelled return {$return->reference}.",
                route('vendor.returns.index'),
                'View returns',
            );
        }

        return $return->fresh();
    }
}
