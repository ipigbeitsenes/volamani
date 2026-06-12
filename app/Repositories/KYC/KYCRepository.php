<?php

namespace App\Repositories\KYC;

use App\Enums\KYCStatus;
use App\Models\KYCVerification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class KYCRepository
{
    public function allForAdmin(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = KYCVerification::with(['user', 'reviewedBy'])->latest('submitted_at');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('reference', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('full_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('id_number', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): ?KYCVerification
    {
        return KYCVerification::with(['user', 'reviewedBy'])->find($id);
    }

    public function pendingCount(): int
    {
        return KYCVerification::where('status', KYCStatus::Pending)->count();
    }
}
