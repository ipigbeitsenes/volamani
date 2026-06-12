<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

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
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ── Roles ────────────────────────────────────────────────────────────

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

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
                'name'     => 'Volamani Admin',
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
