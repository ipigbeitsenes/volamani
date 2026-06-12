<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        // [name, email, username, kind, wallet balance in kobo]
        $users = [
            // Buyers — wallets pre-funded so checkout/escrow can be tested
            ['Chioma Okafor', 'chioma@example.com', 'chioma', 'buyer', 50_000_00],
            ['Tunde Bello',   'tunde@example.com',  'tunde',  'buyer', 30_000_00],
            ['Amaka Eze',     'amaka@example.com',  'amaka',  'buyer', 20_000_00],

            // Vendors
            ['Pixel Forge',     'pixel@example.com',      'pixelforge', 'vendor', 0],
            ['NaijaDev Studio', 'naijadev@example.com',   'naijadev',   'vendor', 0],
            ['BrandCraft',      'brandcraft@example.com', 'brandcraft', 'vendor', 0],
            ['GrowthLab',       'growthlab@example.com',  'growthlab',  'vendor', 0],

            // Consultants (also vendors — consultant profiles attach to a vendor)
            ['Adeola Adewale', 'adeola@example.com', 'adeola', 'consultant', 0],
            ['Emeka Obi',      'emeka@example.com',  'emeka',  'consultant', 0],
        ];

        foreach ($users as [$name, $email, $username, $kind, $balance]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'              => $name,
                    'username'          => $username,
                    'password'          => 'password', // hashed via the model cast
                    'user_type'         => 'individual',
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ],
            );

            $roles = match ($kind) {
                'vendor'     => ['vendor'],
                'consultant' => ['vendor', 'consultant'],
                default      => ['buyer'],
            };
            $user->syncRoles($roles);

            Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => $balance, 'escrow_balance' => 0],
            );
        }

        $this->command->info('Demo users seeded (login with any email above, password: "password").');
    }
}
