<?php

namespace App\Actions\Returns;

use App\Enums\NotificationCategory;
use App\Enums\ReturnStatus;
use App\Models\ReturnRequest;
use App\Services\Notifications\NotificationService;

class MarkReturnShippedAction
{
    public function __construct(private NotificationService $notifications) {}

    public function execute(ReturnRequest $return, ?string $tracking): ReturnRequest
    {
        abort_unless($return->canMarkShipped(), 422, 'You can only add return tracking once the return is approved.');

        $return->update([
            'status'          => ReturnStatus::ShippedBack,
            'return_tracking' => $tracking,
            'shipped_back_at' => now(),
        ]);

        if ($return->vendor?->user) {
            $this->notifications->send(
                $return->vendor->user,
                NotificationCategory::Orders,
                'Return shipped back',
                "The buyer shipped back the item for return {$return->reference}"
                    . ($tracking ? " (tracking: {$tracking})" : '') . '. Confirm receipt to refund.',
                route('vendor.returns.index'),
                'View return',
            );
        }

        return $return->fresh();
    }
}
