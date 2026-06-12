<?php

namespace App\Repositories\Requests;

use App\Models\ProductRequest;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRequestRepository extends BaseRepository
{
    public function __construct(ProductRequest $model)
    {
        parent::__construct($model);
    }

    public function openRequests(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->with(['buyer', 'category'])->open();

        if (!empty($filters['q'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['q'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['q'] . '%');
            });
        }

        if (!empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        if (!empty($filters['budget_max'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereNull('budget_max')
                  ->orWhere('budget_max', '<=', to_kobo($filters['budget_max']));
            });
        }

        if (!empty($filters['deadline'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereNull('deadline_at')
                  ->orWhere('deadline_at', '>=', now()->addDays((int) $filters['deadline']));
            });
        }

        $sort = $filters['sort'] ?? 'latest';
        match ($sort) {
            'budget_high'  => $query->orderByDesc('budget_max'),
            'deadline'     => $query->orderBy('deadline_at'),
            'most_bids'    => $query->orderByDesc('quotations_count'),
            default        => $query->latest(),
        };

        return $query->paginate($perPage)->appends($filters);
    }

    public function buyerRequests(int $buyerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['category', 'acceptedQuotation.vendor'])
            ->where('buyer_id', $buyerId)
            ->latest()
            ->paginate($perPage);
    }

    public function findWithQuotations(int $id): ?ProductRequest
    {
        return $this->model
            ->with(['buyer', 'category', 'quotations.vendor', 'acceptedQuotation.vendor'])
            ->find($id);
    }
}
