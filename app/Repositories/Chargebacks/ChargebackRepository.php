<?php

namespace App\Repositories\Chargebacks;

use App\Models\Chargeback;
use App\Models\Vendor;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ChargebackRepository extends BaseRepository
{
    public function __construct(Chargeback $model)
    {
        parent::__construct($model);
    }

    public function forVendor(Vendor $vendor, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return Chargeback::with(['payment', 'escrow', 'buyer'])
            ->where('vendor_id', $vendor->id)
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function allForAdmin(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return Chargeback::with(['payment', 'escrow', 'buyer', 'vendor'])
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->where('reference', 'like', "%{$term}%")
                ->orWhere('gateway_reference', 'like', "%{$term}%"))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function openCount(): int
    {
        return Chargeback::open()->count();
    }

    public function openCountForVendor(Vendor $vendor): int
    {
        return Chargeback::where('vendor_id', $vendor->id)->open()->count();
    }
}
