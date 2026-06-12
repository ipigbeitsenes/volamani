<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Models\Wallet;
use App\Notifications\WelcomeNotification;
use App\Services\Affiliate\AffiliateService;
use Illuminate\Support\Facades\DB;

class RegisterUserAction
{
    public function execute(array $data, ?string $referralCode = null): User
    {
        return DB::transaction(function () use ($data, $referralCode) {
            $referrer = null;
            if ($referralCode) {
                $referrer = User::where('referral_code', $referralCode)->first();
            }

            $user = User::create([
                'name'        => $data['name'],
                'email'       => $data['email'],
                'password'    => $data['password'],
                'phone'       => $data['phone'] ?? null,
                'user_type'   => $data['user_type'] ?? 'individual',
                'referred_by' => $referrer?->id,
            ]);

            $user->assignRole('buyer');

            // Create wallet for every new user
            Wallet::create([
                'user_id'        => $user->id,
                'balance'        => 0,
                'escrow_balance' => 0,
            ]);

            // Reward the referrer if they run an active affiliate account.
            if ($user->referred_by) {
                app(AffiliateService::class)->recordSignup($user);
            }

            $user->sendEmailVerificationNotification();
            $user->notify(new WelcomeNotification());

            return $user;
        });
    }
}
