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

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];

            // full_name / id_number are encrypted at rest, so a SQL LIKE can never
            // match them. Search the reference and the linked user's name/email
            // (the practical identifiers an admin has) instead.
            $query->where(function ($q) use ($term) {
                $q->where('reference', 'like', '%'.$term.'%')
                    ->orWhereHas('user', function ($u) use ($term) {
                        $u->where('name', 'like', '%'.$term.'%')
                            ->orWhere('email', 'like', '%'.$term.'%');
                    });
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
