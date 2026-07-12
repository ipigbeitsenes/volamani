<?php

namespace App\Actions\Consultations;

use App\Models\ConsultantProfile;
use App\Models\ConsultationPackage;

class CreatePackageAction
{
    public function execute(ConsultantProfile $profile, array $data): ConsultationPackage
    {
        $maxOrder = $profile->allPackages()->max('sort_order') ?? 0;

        return ConsultationPackage::create([
            'profile_id' => $profile->id,
            'name' => $data['name'],
            'description' => $data['description'],
            'type' => $data['type'],
            'duration_minutes' => (int) $data['duration_minutes'],
            'price' => to_kobo((float) $data['price']),
            'max_sessions_per_month' => $data['max_sessions_per_month'] ?? null,
            'is_active' => true,
            'sort_order' => $maxOrder + 1,
        ]);
    }
}
