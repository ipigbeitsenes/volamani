<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminUserSeeder extends Seeder
{
    /**
     * Seeds the platform's privileged accounts. Idempotent (firstOrCreate),
     * so it is safe to re-run. Depends on RolesAndPermissionsSeeder having
     * created the permissions + the 'admin' role first.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Roles ─────────────────────────────────────────────────────────────
        // super-admin holds every permission. admin is created by
        // RolesAndPermissionsSeeder; we firstOrCreate it here too so this seeder
        // can stand alone.
        $superRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superRole->syncPermissions(Permission::all());

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // ── Accounts ──────────────────────────────────────────────────────────
        // [name, email, username, password, roles[]]
        $accounts = [
            ['Volamani Super Admin', 'superadmin@volamani.com', 'superadmin', 'SuperAdmin@123456', ['super-admin', 'admin']],
            ['Volamani Admin',       'admin@volamani.com',      'admin',      'Admin@123456',      ['admin']],
        ];

        foreach ($accounts as [$name, $email, $username, $password, $roles]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'username' => $username,
                    'password' => $password, // hashed via the model cast
                    'user_type' => 'individual',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );

            // super-admin keeps the 'admin' role too so existing role:admin
            // middleware and User::isAdmin() continue to work for it.
            $user->syncRoles($roles);

            Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'escrow_balance' => 0],
            );
        }

        $this->command->info('Admin & Super Admin users seeded.');
        $this->command->info('  super-admin: superadmin@volamani.com / SuperAdmin@123456');
        $this->command->info('  admin:       admin@volamani.com / Admin@123456');
    }
}
