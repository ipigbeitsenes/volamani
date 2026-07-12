<?php

namespace App\Actions\Matching;

use App\Models\MatchingProfile;
use App\Models\Vendor;

class UpsertMatchingProfileAction
{
    public function execute(Vendor $vendor, array $data): MatchingProfile
    {
        return MatchingProfile::updateOrCreate(
            ['vendor_id' => $vendor->id],
            [
                'headline' => $data['headline'] ?? null,
                'bio' => $data['bio'] ?? null,
                'categories' => $data['categories'] ?? null,
                'skills' => $data['skills'] ?? null,
                'min_budget' => $data['min_budget'] ?? null,
                'max_budget' => $data['max_budget'] ?? null,
                'serves_remote' => $data['serves_remote'] ?? true,
                'locations' => $data['locations'] ?? null,
                'is_accepting' => $data['is_accepting'] ?? true,
            ],
        );
    }
}
