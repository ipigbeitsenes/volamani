<?php

namespace App\Actions\Consultations;

use App\Enums\ConsultationSessionStatus;
use App\Enums\PaymentStatus;
use App\Models\ConsultantProfile;
use App\Models\ConsultationPackage;
use App\Models\ConsultationSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BookConsultationAction
{
    public function execute(ConsultantProfile $profile, ConsultationPackage $package, User $buyer, array $data): ConsultationSession
    {
        abort_unless($profile->is_available, 422, 'This consultant is not currently accepting bookings.');
        abort_if($profile->vendor->user_id === $buyer->id, 403, 'You cannot book your own consultation.');

        return DB::transaction(function () use ($profile, $package, $buyer, $data) {
            $commissionRate = $profile->vendor->getEffectiveCommissionRate();
            $platformFee = (int) round($package->price * ($commissionRate / 100));
            $consultantEarnings = $package->price - $platformFee;

            return ConsultationSession::create([
                'package_id' => $package->id,
                'profile_id' => $profile->id,
                'buyer_id' => $buyer->id,
                'status' => ConsultationSessionStatus::Pending,
                'payment_status' => PaymentStatus::Pending,
                'price' => $package->price,
                'platform_fee' => $platformFee,
                'consultant_earnings' => $consultantEarnings,
                'scheduled_at' => $data['scheduled_at'],
                'duration_minutes' => $package->duration_minutes,
                'meeting_platform' => $data['meeting_platform'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }
}
