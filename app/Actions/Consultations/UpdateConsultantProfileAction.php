<?php

namespace App\Actions\Consultations;

use App\Models\ConsultantAvailability;
use App\Models\ConsultantProfile;
use Illuminate\Support\Facades\DB;

class UpdateConsultantProfileAction
{
    public function execute(ConsultantProfile $profile, array $data): ConsultantProfile
    {
        return DB::transaction(function () use ($profile, $data) {
            $profile->update([
                'display_name' => $data['display_name'] ?? $profile->display_name,
                'bio' => $data['bio'] ?? $profile->bio,
                'niche' => $data['niche'] ?? $profile->niche,
                'expertise' => isset($data['expertise'])
                    ? array_filter(array_map('trim', explode(',', $data['expertise'])))
                    : $profile->expertise,
                'experience_years' => $data['experience_years'] ?? $profile->experience_years,
                'linkedin' => $data['linkedin'] ?? $profile->linkedin,
                'calendly_url' => $data['calendly_url'] ?? $profile->calendly_url,
                'is_available' => isset($data['is_available'])
                    ? (bool) $data['is_available']
                    : $profile->is_available,
            ]);

            if (isset($data['availability'])) {
                $profile->availability()->update(['is_active' => false]);
                foreach ($data['availability'] as $day => $slot) {
                    if (empty($slot['enabled'])) {
                        ConsultantAvailability::where('profile_id', $profile->id)
                            ->where('day_of_week', (int) $day)
                            ->update(['is_active' => false]);

                        continue;
                    }
                    ConsultantAvailability::updateOrCreate(
                        ['profile_id' => $profile->id, 'day_of_week' => (int) $day],
                        [
                            'start_time' => $slot['start_time'],
                            'end_time' => $slot['end_time'],
                            'is_active' => true,
                        ]
                    );
                }
            }

            return $profile->fresh(['availability']);
        });
    }
}
