<?php

namespace App\Actions\Taxonomy;

use App\Enums\CategoryRequestStatus;
use App\Models\CategoryRequest;
use App\Models\User;

class RejectCategoryRequestAction
{
    public function execute(CategoryRequest $request, User $admin, ?string $note = null): CategoryRequest
    {
        if (! $request->isPending()) {
            return $request;
        }

        $request->update([
            'status'      => CategoryRequestStatus::Rejected,
            'admin_note'  => $note,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        return $request->fresh();
    }
}
