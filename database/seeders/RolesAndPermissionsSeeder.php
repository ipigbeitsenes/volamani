<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions grouped by module
        $permissions = [
            // Products
            'products.view', 'products.create', 'products.edit', 'products.delete', 'products.approve',
            // Services
            'services.view', 'services.create', 'services.edit', 'services.delete',
            // Orders
            'orders.view', 'orders.manage',
            // Wallet
            'wallet.view', 'wallet.fund', 'wallet.withdraw',
            // Withdrawals (admin)
            'withdrawals.approve', 'withdrawals.reject',
            // KYC
            'kyc.view', 'kyc.submit', 'kyc.approve', 'kyc.reject',
            // Disputes
            'disputes.create', 'disputes.view', 'disputes.resolve',
            // Users (admin)
            'users.view', 'users.manage',
            // Vendors
            'vendors.approve', 'vendors.suspend',
            // Settings
            'settings.manage',
            // Commissions
            'commissions.manage',
            // Returns / RMA
            'returns.manage',
            // Escrow
            'escrows.manage',
            // Payments (read / offline approval)
            'payments.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ── Roles ────────────────────────────────────────────────────────────

        // Capabilities reserved for super-admin only. A regular admin gets
        // every other permission; the super-admin role (seeded in
        // AdminUserSeeder) holds Permission::all().
        $superAdminOnly = [
            'users.manage',                          // managing users / other admins
            'withdrawals.approve', 'withdrawals.reject', // payout approvals
            'commissions.manage',                    // commission rates
            'settings.manage',                       // platform settings
        ];

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all()->whereNotIn('name', $superAdminOnly));

        $moderator = Role::firstOrCreate(['name' => 'moderator', 'guard_name' => 'web']);
        $moderator->syncPermissions([
            'products.view', 'products.approve',
            'services.view',
            'orders.view',
            'kyc.view', 'kyc.approve', 'kyc.reject',
            'disputes.view', 'disputes.resolve',
            'users.view',
            'vendors.approve', 'vendors.suspend',
        ]);

        $vendor = Role::firstOrCreate(['name' => 'vendor', 'guard_name' => 'web']);
        $vendor->syncPermissions([
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'services.view', 'services.create', 'services.edit', 'services.delete',
            'orders.view', 'orders.manage',
            'wallet.view', 'wallet.withdraw',
            'kyc.view', 'kyc.submit',
            'disputes.create', 'disputes.view',
        ]);

        $buyer = Role::firstOrCreate(['name' => 'buyer', 'guard_name' => 'web']);
        $buyer->syncPermissions([
            'products.view',
            'services.view',
            'orders.view',
            'wallet.view', 'wallet.fund', 'wallet.withdraw',
            'kyc.view', 'kyc.submit',
            'disputes.create', 'disputes.view',
        ]);

        // Support team — handles buyer/vendor issues: support tickets (disputes),
        // returns/RMA, and KYC verification.
        $support = Role::firstOrCreate(['name' => 'support', 'guard_name' => 'web']);
        $support->syncPermissions([
            'disputes.view', 'disputes.resolve',
            'returns.manage',
            'kyc.view', 'kyc.approve', 'kyc.reject',
            'users.view',
        ]);

        // Finance team — handles money movement: payments, withdrawals/payouts,
        // escrow release/refund, and commission rates.
        $finance = Role::firstOrCreate(['name' => 'finance', 'guard_name' => 'web']);
        $finance->syncPermissions([
            'payments.view',
            'withdrawals.approve', 'withdrawals.reject',
            'escrows.manage',
            'commissions.manage',
            'wallet.view',
        ]);

        $consultant = Role::firstOrCreate(['name' => 'consultant', 'guard_name' => 'web']);
        $consultant->syncPermissions([
            'services.view', 'services.create', 'services.edit',
            'orders.view', 'orders.manage',
            'wallet.view', 'wallet.withdraw',
        ]);

        // ── Default Admin User ────────────────────────────────────────────────
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@volamani.com'],
            [
                'name' => 'Volamani Admin',
                'password' => bcrypt('Admin@123456'),
                'username' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $adminUser->assignRole('admin');

        $this->command->info('Roles, permissions and admin user seeded.');
    }
}
