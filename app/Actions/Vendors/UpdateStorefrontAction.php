<?php

namespace App\Actions\Vendors;

use App\Models\Vendor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateStorefrontAction
{
    public function execute(Vendor $vendor, array $data, ?UploadedFile $logo = null, ?UploadedFile $banner = null): Vendor
    {
        if ($logo) {
            if ($vendor->logo) {
                Storage::disk('public')->delete($vendor->logo);
            }
            $data['logo'] = $logo->store('vendors/logos', 'public');
        }

        if ($banner) {
            if ($vendor->banner) {
                Storage::disk('public')->delete($vendor->banner);
            }
            $data['banner'] = $banner->store('vendors/banners', 'public');
        }

        $vendor->update($data);

        return $vendor->fresh();
    }
}
