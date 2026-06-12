<?php

namespace App\Services\Admin;

use App\Enums\NotificationCategory;
use App\Enums\Status;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use App\Models\WalletWithdrawal;
use App\Repositories\Admin\AdminRepository;
use App\Services\Notifications\NotificationService;
use App\Services\Payment\PaymentService;
use App\Services\Products\ProductService;
use App\Services\Wallet\WalletService;

class AdminService
{
    public function __construct(
        private AdminRepository     $repo,
        private NotificationService $notifications,
        private WalletService       $wallet,
        private ProductService      $products,
        private PaymentService      $payments,
    ) {}

    // ─── Dashboard / reads ──────────────────────────────────────────────────────

    public function dashboardStats(): array
    {
        return $this->repo->dashboardStats();
    }

    public function revenueByDay(int $days = 14): array
    {
        return $this->repo->revenueByDay($days);
    }

    public function users(array $filters = [], int $perPage = 20)
    {
        return $this->repo->users($filters, $perPage);
    }

    public function findUser(int $id): ?User
    {
        return $this->repo->findUser($id);
    }

    public function vendors(array $filters = [], int $perPage = 20)
    {
        return $this->repo->vendors($filters, $perPage);
    }

    public function vendorCountsByStatus(): array
    {
        return $this->repo->vendorCountsByStatus();
    }

    public function payments(array $filters = [], int $perPage = 20)
    {
        return $this->repo->payments($filters, $perPage);
    }

    public function settingsGrouped()
    {
        return $this->repo->settingsGrouped();
    }

    public function auditLogs(array $filters = [], int $perPage = 30)
    {
        return $this->repo->auditLogs($filters, $perPage);
    }

    public function auditLogNames(): array
    {
        return $this->repo->auditLogNames();
    }

    // ─── User management ──────────────────────────────────────────────────────────

    public function setUserActive(User $user, bool $active): void
    {
        $user->update(['is_active' => $active]);
    }

    public function deleteUser(User $user): void
    {
        $user->delete();
    }

    // ─── Vendor moderation ────────────────────────────────────────────────────────

    public function approveVendor(Vendor $vendor, User $admin): void
    {
        $vendor->update([
            'status'           => Status::Active,
            'approved_at'      => now(),
            'approved_by'      => $admin->id,
            'rejection_reason' => null,
        ]);

        $vendor->user?->assignRole('vendor');

        if ($vendor->user) {
            $this->notifications->send(
                $vendor->user,
                NotificationCategory::Verification,
                'Your store is approved',
                'Congratulations — your vendor store "' . $vendor->business_name . '" is now live on Volamani.',
                route('vendor.dashboard'),
                'Go to dashboard',
            );
        }
    }

    public function rejectVendor(Vendor $vendor, User $admin, string $reason): void
    {
        $vendor->update([
            'status'           => Status::Inactive,
            'approved_by'      => $admin->id,
            'rejection_reason' => $reason,
        ]);

        if ($vendor->user) {
            $this->notifications->send(
                $vendor->user,
                NotificationCategory::Verification,
                'Store application declined',
                'Your vendor application was not approved: ' . $reason,
                route('vendor.dashboard'),
            );
        }
    }

    public function suspendVendor(Vendor $vendor, User $admin, string $reason): void
    {
        $vendor->update([
            'status'           => Status::Suspended,
            'approved_by'      => $admin->id,
            'rejection_reason' => $reason,
        ]);

        if ($vendor->user) {
            $this->notifications->send(
                $vendor->user,
                NotificationCategory::Verification,
                'Store suspended',
                'Your vendor store has been suspended: ' . $reason . ' Contact support for details.',
                route('vendor.dashboard'),
            );
        }
    }

    // ─── Withdrawals ────────────────────────────────────────────────────────────────

    public function approveWithdrawal(WalletWithdrawal $withdrawal, User $admin): void
    {
        $this->wallet->approveWithdrawal($withdrawal, $admin);

        $this->notifications->send(
            $withdrawal->user,
            NotificationCategory::Payments,
            'Withdrawal approved',
            'Your withdrawal of ' . money($withdrawal->amount) . ' has been approved and is being paid out.',
            route('wallet.index'),
        );
    }

    public function rejectWithdrawal(WalletWithdrawal $withdrawal, User $admin, string $reason): void
    {
        $this->wallet->rejectWithdrawal($withdrawal, $admin, $reason);

        $this->notifications->send(
            $withdrawal->user,
            NotificationCategory::Payments,
            'Withdrawal declined',
            'Your withdrawal of ' . money($withdrawal->amount) . ' was declined: ' . $reason . ' The funds have been returned to your wallet.',
            route('wallet.index'),
        );
    }

    // ─── Product moderation ────────────────────────────────────────────────────────

    public function approveProduct(Product $product, User $admin): void
    {
        $this->products->approveProduct($product, $admin);

        if ($product->vendor?->user) {
            $this->notifications->send(
                $product->vendor->user,
                NotificationCategory::Account,
                'Product approved',
                'Your product "' . $product->name . '" is now live in the marketplace.',
                route('vendor.products.index'),
            );
        }
    }

    public function rejectProduct(Product $product, User $admin, string $reason): void
    {
        $this->products->rejectProduct($product, $admin, $reason);

        if ($product->vendor?->user) {
            $this->notifications->send(
                $product->vendor->user,
                NotificationCategory::Account,
                'Product needs changes',
                'Your product "' . $product->name . '" was not approved: ' . $reason,
                route('vendor.products.index'),
            );
        }
    }

    public function deleteProduct(Product $product): void
    {
        $product->delete();
    }

    // ─── Payments ────────────────────────────────────────────────────────────────────

    public function approveOfflinePayment(\App\Models\Payment $payment, User $admin): bool
    {
        $proof = $payment->bankTransferProof()->where('status', 'pending')->latest()->first();

        if (! $proof) {
            return false;
        }

        $this->payments->approveBankTransfer($proof, $admin);

        return true;
    }

    // ─── Settings & commissions ────────────────────────────────────────────────────

    public function updateSettings(array $input): void
    {
        foreach (Setting::all() as $setting) {
            if (! array_key_exists($setting->key, $input)) {
                continue;
            }

            $value = match ($setting->type) {
                'boolean' => (bool) ((int) $input[$setting->key]),
                'integer' => (int) $input[$setting->key],
                default   => $input[$setting->key],
            };

            Setting::set($setting->key, $value, $setting->type);
        }
    }

    public function updateCommissions(array $input): void
    {
        foreach (['platform_commission', 'affiliate_commission', 'withdrawal_fee', 'min_withdrawal'] as $key) {
            if (isset($input[$key]) && $input[$key] !== '') {
                Setting::set($key, (int) $input[$key], 'integer');
            }
        }
    }
}
