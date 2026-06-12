<?php

namespace App\Actions\Vendors;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class CreateVendorAction
{
    public function execute(User $user, array $data): Vendor
    {
        return DB::transaction(function () use ($user, $data) {
            $vendor = Vendor::create([
                'user_id'       => $user->id,
                'business_name' => $data['business_name'],
                'tagline'       => $data['tagline'] ?? null,
                'description'   => $data['description'] ?? null,
                'category'      => $data['category'] ?? null,
                'whatsapp'      => $data['whatsapp'] ?? $user->whatsapp,
                'city'          => $data['city'] ?? null,
                'state'         => $data['state'] ?? null,
                'status'        => 'pending',
            ]);

            // Assign vendor role (in addition to existing buyer role)
            if (! $user->hasRole('vendor')) {
                $user->assignRole('vendor');
            }

            return $vendor;
        });
    }
}
