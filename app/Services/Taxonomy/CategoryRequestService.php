<?php

namespace App\Services\Taxonomy;

use App\Actions\Taxonomy\ApproveCategoryRequestAction;
use App\Actions\Taxonomy\RejectCategoryRequestAction;
use App\Actions\Taxonomy\SubmitCategoryRequestAction;
use App\Models\CategoryRequest;
use App\Models\User;
use App\Models\Vendor;
use App\Repositories\Taxonomy\CategoryRequestRepository;
use App\Services\BaseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryRequestService extends BaseService
{
    public function __construct(
        private CategoryRequestRepository $repository,
        private SubmitCategoryRequestAction $submitAction,
        private ApproveCategoryRequestAction $approveAction,
        private RejectCategoryRequestAction $rejectAction,
    ) {}

    public function submit(Vendor $vendor, array $data): CategoryRequest
    {
        return $this->submitAction->execute($vendor, $data);
    }

    public function approve(CategoryRequest $request, User $admin, ?string $note = null): CategoryRequest
    {
        return $this->approveAction->execute($request, $admin, $note);
    }

    public function reject(CategoryRequest $request, User $admin, ?string $note = null): CategoryRequest
    {
        return $this->rejectAction->execute($request, $admin, $note);
    }

    public function forVendor(Vendor $vendor): LengthAwarePaginator
    {
        return $this->repository->forVendor($vendor->id);
    }

    public function allForAdmin(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->allForAdmin($filters);
    }

    public function pendingCount(): int
    {
        return $this->repository->pendingCount();
    }
}
