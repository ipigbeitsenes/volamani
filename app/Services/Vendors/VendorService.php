<?php

namespace App\Services\Vendors;

use App\Actions\Vendors\CreateVendorAction;
use App\Actions\Vendors\UpdateStorefrontAction;
use App\Models\User;
use App\Models\Vendor;
use App\Services\BaseService;
use Illuminate\Http\UploadedFile;

class VendorService extends BaseService
{
    public function __construct(
        private CreateVendorAction $createAction,
        private UpdateStorefrontAction $updateAction,
    ) {}

    public function createVendor(User $user, array $data): Vendor
    {
        return $this->createAction->execute($user, $data);
    }

    public function updateStorefront(Vendor $vendor, array $data, ?UploadedFile $logo = null, ?UploadedFile $banner = null): Vendor
    {
        return $this->updateAction->execute($vendor, $data, $logo, $banner);
    }
}
