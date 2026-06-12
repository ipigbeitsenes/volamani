<?php

namespace App\Actions\Consultations;

use App\Models\ConsultantAvailability;
use App\Models\ConsultantProfile;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class CreateConsultantProfileAction
{
    public function execute(Vendor $vendor, array $data): ConsultantProfile
    {
        return DB::transaction(function () use ($vendor, $data) {
            $profile = ConsultantProfile::create([
                'vendor_id'        => $vendor->id,
                'display_name'     => $data['display_name'],
                'bio'              => $data['bio'],
                'niche'            => $data['niche'] ?? null,
                'expertise'        => array_filter(explode(',', $data['expertise'] ?? '')),
                'experience_years' => $data['experience_years'] ?? 1,
                'linkedin'         => $data['linkedin'] ?? null,
                'calendly_url'     => $data['calendly_url'] ?? null,
                'is_available'     => true,
            ]);

            if (!empty($data['availability'])) {
                $this->syncAvailability($profile, $data['availability']);
            }

            return $profile;
        });
    }

    private function syncAvailability(ConsultantProfile $profile, array $slots): void
    {
        foreach ($slots as $day => $slot) {
            if (empty($slot['enabled'])) {
                continue;
            }
            ConsultantAvailability::updateOrCreate(
                ['profile_id' => $profile->id, 'day_of_week' => (int) $day],
                [
                    'start_time' => $slot['start_time'],
                    'end_time'   => $slot['end_time'],
                    'is_active'  => true,
                ]
            );
        }
    }
}
