<?php

namespace App\Repositories\Taxonomy;

use App\Models\CategoryRequest;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryRequestRepository extends BaseRepository
{
    public function __construct(CategoryRequest $model)
    {
        parent::__construct($model);
    }

    public function allForAdmin(array $filters = []): LengthAwarePaginator
    {
        return CategoryRequest::query()
            ->with(['vendor', 'reviewedBy'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['domain'] ?? null, fn ($q, $domain) => $q->where('domain', $domain))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();
    }

    public function forVendor(int $vendorId): LengthAwarePaginator
    {
        return CategoryRequest::query()
            ->where('vendor_id', $vendorId)
            ->latest()
            ->paginate(15);
    }

    public function pendingCount(): int
    {
        return CategoryRequest::pending()->count();
    }
}
