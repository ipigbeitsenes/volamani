<?php

namespace App\Repositories\Pricing;

use App\Models\PricingAddOn;
use App\Models\PricingEstimate;
use App\Models\PricingTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PricingRepository
{
    public function templatesByCategory(string $category): Collection
    {
        return PricingTemplate::where('category', $category)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('base_price')
            ->get();
    }

    public function addOnsByCategory(string $category): Collection
    {
        return PricingAddOn::where(function ($q) use ($category) {
            $q->where('category', $category)->orWhereNull('category');
        })
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function allTemplatesGrouped(): array
    {
        return PricingTemplate::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category')
            ->toArray();
    }

    public function findEstimate(string $reference): ?PricingEstimate
    {
        return PricingEstimate::with('template')->where('reference', $reference)->first();
    }

    public function userEstimates(int $userId): LengthAwarePaginator
    {
        return PricingEstimate::where('user_id', $userId)
            ->latest()
            ->paginate(10);
    }

    public function sessionEstimates(string $token): Collection
    {
        return PricingEstimate::where('session_token', $token)->latest()->get();
    }
}
