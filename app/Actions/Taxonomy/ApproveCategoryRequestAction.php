<?php

namespace App\Actions\Taxonomy;

use App\Enums\CategoryRequestStatus;
use App\Models\CategoryRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApproveCategoryRequestAction
{
    /**
     * Approve a request: create the category in its domain's tree, then mark
     * the request approved and link it to the new category.
     */
    public function execute(CategoryRequest $request, User $admin, ?string $note = null): CategoryRequest
    {
        if (! $request->isPending()) {
            return $request;
        }

        return DB::transaction(function () use ($request, $admin, $note) {
            $modelClass = $request->domain->modelClass();

            $category = $modelClass::create([
                'parent_id' => $request->parent_id,
                'name'      => $request->name,
                'is_active' => true,
            ]);

            $request->update([
                'status'              => CategoryRequestStatus::Approved,
                'admin_note'          => $note,
                'reviewed_by'         => $admin->id,
                'reviewed_at'         => now(),
                'created_category_id' => $category->id,
            ]);

            return $request->fresh();
        });
    }
}
