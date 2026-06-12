<?php

namespace App\Repositories\Admin;

use App\Enums\KYCStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Enums\Status;
use App\Enums\WithdrawalStatus;
use App\Models\BankTransferProof;
use App\Models\Dispute;
use App\Models\KYCVerification;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use App\Models\WalletWithdrawal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class AdminRepository
{
    /** Headline counters + work queues + recent activity for the dashboard. */
    public function dashboardStats(): array
    {
        return [
            'users'          => User::count(),
            'vendors_active' => Vendor::where('status', Status::Active)->count(),
            'orders'         => Order::count(),
            'revenue'        => (int) Payment::where('status', PaymentStatus::Success)->sum('amount'),
            'queues'         => [
                'vendors'        => Vendor::where('status', Status::Pending)->count(),
                'kyc'            => KYCVerification::where('status', KYCStatus::Pending)->count(),
                'withdrawals'    => WalletWithdrawal::where('status', WithdrawalStatus::Pending)->count(),
                'products'       => Product::where('status', ProductStatus::Pending)->count(),
                'disputes'       => Dispute::whereIn('status', ['open', 'under_review', 'awaiting_response', 'escalated'])->count(),
                'bank_transfers' => BankTransferProof::where('status', 'pending')->count(),
            ],
            'recent_users'    => User::latest()->limit(6)->get(),
            'recent_payments' => Payment::with('user')->latest()->limit(6)->get(),
        ];
    }

    /** Revenue trend for the last N days (date => kobo). */
    public function revenueByDay(int $days = 14): array
    {
        $rows = Payment::where('status', PaymentStatus::Success)
            ->where('created_at', '>=', now()->subDays($days)->startOfDay())
            ->select(DB::raw('DATE(created_at) as d'), DB::raw('SUM(amount) as total'))
            ->groupBy('d')
            ->pluck('total', 'd');

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $series[$date] = (int) ($rows[$date] ?? 0);
        }

        return $series;
    }

    // ─── Users ─────────────────────────────────────────────────────────────────

    public function users(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = User::with('roles')->latest();

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('username', 'like', "%{$term}%"));
        }

        if (! empty($filters['role'])) {
            $query->role($filters['role']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', $filters['status'] === 'active');
        }

        return $query->paginate($perPage);
    }

    public function findUser(int $id): ?User
    {
        return User::with(['roles', 'vendor', 'wallet'])->find($id);
    }

    // ─── Vendors ─────────────────────────────────────────────────────────────────

    public function vendors(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Vendor::with('user')->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(fn ($q) => $q->where('business_name', 'like', "%{$term}%")
                ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$term}%")));
        }

        return $query->paginate($perPage);
    }

    public function vendorCountsByStatus(): array
    {
        return Vendor::select('status', DB::raw('count(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status')
            ->toArray();
    }

    // ─── Settings ─────────────────────────────────────────────────────────────────

    /** All platform settings grouped by their `group` column. */
    public function settingsGrouped(): Collection
    {
        return Setting::orderBy('group')->orderBy('id')->get()->groupBy('group');
    }

    // ─── Audit log ─────────────────────────────────────────────────────────────────

    public function auditLogs(array $filters = [], int $perPage = 30): LengthAwarePaginator
    {
        $query = Activity::with('causer')->latest();

        if (! empty($filters['log'])) {
            $query->where('log_name', $filters['log']);
        }

        if (! empty($filters['search'])) {
            $query->where('description', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($perPage);
    }

    public function auditLogNames(): array
    {
        return Activity::query()->distinct()->whereNotNull('log_name')->pluck('log_name')->all();
    }

    public function payments(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Payment::with(['user', 'payable'])->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['gateway'])) {
            $query->where('gateway', $filters['gateway']);
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(fn ($q) => $q->where('reference', 'like', "%{$term}%")
                ->orWhere('gateway_reference', 'like', "%{$term}%"));
        }

        return $query->paginate($perPage);
    }
}
