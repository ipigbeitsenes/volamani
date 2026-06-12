<?php

namespace App\Repositories\Wallet;

use App\Enums\WithdrawalStatus;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletWithdrawal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WalletRepository
{
    public function paginateLedger(Wallet $wallet, int $perPage = 20): LengthAwarePaginator
    {
        return $wallet->ledgers()
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function pendingWithdrawals(int $perPage = 20): LengthAwarePaginator
    {
        return WalletWithdrawal::with(['user', 'wallet'])
            ->where('status', WithdrawalStatus::Pending)
            ->latest()
            ->paginate($perPage);
    }

    public function userWithdrawals(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return WalletWithdrawal::where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    public function allWithdrawalsForAdmin(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = WalletWithdrawal::with(['user', 'processedBy'])
            ->latest();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->paginate($perPage);
    }

    public function findWithdrawal(int $id): ?WalletWithdrawal
    {
        return WalletWithdrawal::with(['user', 'wallet', 'processedBy'])->find($id);
    }
}
