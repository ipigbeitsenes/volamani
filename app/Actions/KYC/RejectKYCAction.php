<?php

namespace App\Actions\KYC;

use App\Enums\KYCStatus;
use App\Enums\NotificationCategory;
use App\Models\KYCVerification;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;

class RejectKYCAction
{
    public function __construct(private NotificationService $notifications) {}

    public function execute(KYCVerification $kyc, User $admin, string $reason): KYCVerification
    {
        abort_unless($kyc->canReview(), 422, 'This verification is not awaiting review.');

        $kyc = DB::transaction(function () use ($kyc, $admin, $reason) {
            $kyc->update([
                'status' => KYCStatus::Rejected,
                'rejection_reason' => $reason,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'verified_at' => null,
            ]);

            $kyc->user->update(['kyc_status' => KYCStatus::Rejected]);

            return $kyc->fresh();
        });

        $this->notifications->send(
            $kyc->user,
            NotificationCategory::Verification,
            'Verification needs attention',
            'Your KYC submission was not approved: '.$reason.' You can resubmit with corrected details.',
            route('kyc.index'),
            'Resubmit',
        );

        return $kyc;
    }
}
