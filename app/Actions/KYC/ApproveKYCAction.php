<?php

namespace App\Actions\KYC;

use App\Enums\KYCStatus;
use App\Enums\NotificationCategory;
use App\Models\KYCVerification;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;

class ApproveKYCAction
{
    public function __construct(private NotificationService $notifications) {}

    public function execute(KYCVerification $kyc, User $admin): KYCVerification
    {
        abort_unless($kyc->canReview(), 422, 'This verification is not awaiting review.');

        $kyc = DB::transaction(function () use ($kyc, $admin) {
            $kyc->update([
                'status'           => KYCStatus::Verified,
                'rejection_reason' => null,
                'reviewed_by'      => $admin->id,
                'reviewed_at'      => now(),
                'verified_at'      => now(),
            ]);

            $kyc->user->update(['kyc_status' => KYCStatus::Verified]);

            return $kyc->fresh();
        });

        $this->notifications->send(
            $kyc->user,
            NotificationCategory::Verification,
            'Identity verified',
            'Your KYC verification has been approved. Your account is now verified.',
            route('kyc.index'),
            'View status',
        );

        return $kyc;
    }
}
