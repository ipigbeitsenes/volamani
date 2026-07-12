<?php

namespace App\Actions\Taxonomy;

use App\Enums\CategoryRequestStatus;
use App\Models\CategoryRequest;
use App\Models\Vendor;

class SubmitCategoryRequestAction
{
    public function execute(Vendor $vendor, array $data): CategoryRequest
    {
        return CategoryRequest::create([
            'vendor_id' => $vendor->id,
            'domain' => $data['domain'],
            'name' => $data['name'],
            'parent_id' => $data['parent_id'] ?? null,
            'reason' => $data['reason'] ?? null,
            'status' => CategoryRequestStatus::Pending,
        ]);
    }
}
