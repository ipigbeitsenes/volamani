<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Http\Request;

class LoginUserAction
{
    public function execute(User $user, Request $request): void
    {
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);
    }
}
