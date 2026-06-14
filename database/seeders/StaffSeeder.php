<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class StaffSeeder extends Seeder
{
    /**
     * Seeds the platform's internal staff accounts (support + finance teams).
     * Idempotent (firstOrCreate). Depends on RolesAndPermissionsSeeder having
     * created the 'support' and 'finance' roles + their permissions first.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Ensure the roles exist even if this seeder runs standalone.
        Role::firstOrCreate(['name' => 'support', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'finance', 'guard_name' => 'web']);

        // [name, email, username, password, role]
        $accounts = [
            ['Volamani Support', 'support@volamani.com', 'support', 'Support@123456', 'support'],
            ['Volamani Finance', 'finance@volamani.com', 'finance', 'Finance@123456', 'finance'],
        ];

        foreach ($accounts as [$name, $email, $username, $password, $role]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'              => $name,
                    'username'          => $username,
                    'password'          => $password, // hashed via the model cast
                    'user_type'         => 'individual',
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles([$role]);

            Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'escrow_balance' => 0],
            );
        }

        $this->command->info('Staff users seeded.');
        $this->command->info('  support: support@volamani.com / Support@123456');
        $this->command->info('  finance: finance@volamani.com / Finance@123456');
    }
}
