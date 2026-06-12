<?php

namespace App\Repositories\Affiliate;

use App\Enums\CommissionStatus;
use App\Models\AffiliateAccount;
use App\Models\AffiliateCommission;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AffiliateRepository
{
    public function accountForUser(User $user): ?AffiliateAccount
    {
        return AffiliateAccount::where('user_id', $user->id)->first();
    }

    /** Resolve an active affiliate account from a shareable code (a user's referral_code). */
    public function activeAccountByCode(string $code): ?AffiliateAccount
    {
        $user = User::where('referral_code', $code)->first();

        if (! $user) {
            return null;
        }

        $account = $user->affiliateAccount;

        return $account && $account->isActive() ? $account : null;
    }

    public function commissionsForAccount(AffiliateAccount $account, int $perPage = 15): LengthAwarePaginator
    {
        return $account->commissions()
            ->with('buyer')
            ->paginate($perPage);
    }

    public function referralsForAccount(AffiliateAccount $account, int $perPage = 15): LengthAwarePaginator
    {
        return $account->referrals()
            ->with('referredUser')
            ->paginate($perPage);
    }

    public function topAffiliates(int $limit = 10): Collection
    {
        return AffiliateAccount::with('user')
            ->orderByDesc('total_earned')
            ->limit($limit)
            ->get();
    }

    public function allAccountsForAdmin(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = AffiliateAccount::with('user')->orderByDesc('total_earned');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->whereHas('user', function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('referral_code', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function commissionsForAdmin(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = AffiliateCommission::with(['account.user', 'buyer'])->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->paginate($perPage);
    }

    public function pendingCommissionsCount(): int
    {
        return AffiliateCommission::where('status', CommissionStatus::Pending)->count();
    }

    public function pendingPayoutTotal(): int
    {
        return (int) AffiliateCommission::where('status', CommissionStatus::Pending)->sum('amount');
    }
}
