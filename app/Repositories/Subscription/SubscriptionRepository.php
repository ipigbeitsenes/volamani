<?php

namespace App\Repositories\Subscription;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionRepository
{
    public function activePlans(): Collection
    {
        return SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();
    }

    public function allPlans(): Collection
    {
        return SubscriptionPlan::withCount('subscriptions')
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();
    }

    public function planBySlug(string $slug): ?SubscriptionPlan
    {
        return SubscriptionPlan::where('slug', $slug)->first();
    }

    public function allForAdmin(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = Subscription::with(['vendor', 'plan', 'user'])->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['plan'])) {
            $query->where('plan_id', $filters['plan']);
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->whereHas('vendor', fn ($q) => $q->where('business_name', 'like', "%{$term}%"))
                ->orWhere('reference', 'like', "%{$term}%");
        }

        return $query->paginate($perPage);
    }

    /** Subscriptions whose current period has lapsed and need billing/expiry. */
    public function dueForProcessing(): Collection
    {
        return Subscription::with(['plan', 'user', 'vendor'])
            ->whereIn('status', [
                SubscriptionStatus::Active,
                SubscriptionStatus::Trialing,
                SubscriptionStatus::PastDue,
                SubscriptionStatus::Cancelled,
            ])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->get();
    }

    public function stats(): array
    {
        $base = Subscription::query();

        return [
            'active' => (clone $base)->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Trialing])->count(),
            'past_due' => (clone $base)->where('status', SubscriptionStatus::PastDue)->count(),
            'cancelled' => (clone $base)->where('status', SubscriptionStatus::Cancelled)->count(),
            'mrr' => (int) (clone $base)->where('status', SubscriptionStatus::Active)->sum('price'),
        ];
    }
}
