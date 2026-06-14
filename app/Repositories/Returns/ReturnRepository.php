<?php

namespace App\Repositories\Returns;

use App\Models\ReturnRequest;
use App\Models\User;
use App\Models\Vendor;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReturnRepository extends BaseRepository
{
    public function __construct(ReturnRequest $model)
    {
        parent::__construct($model);
    }

    public function forBuyer(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return ReturnRequest::with(['order', 'vendor'])
            ->where('buyer_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    public function forVendor(Vendor $vendor, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return ReturnRequest::with(['order', 'buyer'])
            ->where('vendor_id', $vendor->id)
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function allForAdmin(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return ReturnRequest::with(['order', 'buyer', 'vendor'])
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->where('reference', 'like', "%{$term}%"))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function pendingCountForVendor(Vendor $vendor): int
    {
        return ReturnRequest::where('vendor_id', $vendor->id)->active()->count();
    }
}
