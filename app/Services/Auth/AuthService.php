<?php

namespace App\Services\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\Models\User;
use App\Services\BaseService;
use App\Services\Security\SecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService extends BaseService
{
    public function __construct(
        private RegisterUserAction $registerAction,
        private LoginUserAction $loginAction,
        private SecurityService $security,
    ) {}

    public function register(array $data, ?string $referralCode = null): User
    {
        return $this->registerAction->execute($data, $referralCode);
    }

    public function attemptLogin(array $credentials, bool $remember, Request $request): User
    {
        // Block locked accounts before even checking the password.
        if ($this->security->isEmailLocked($credentials['email'] ?? null)) {
            throw ValidationException::withMessages([
                'email' => 'Your account is temporarily locked due to repeated failed sign-ins. Please try again later.',
            ]);
        }

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Your account has been suspended. Please contact support.',
            ]);
        }

        $this->loginAction->execute($user, $request);

        return $user;
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function sendPasswordResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    public function resetPassword(array $data): string
    {
        return Password::reset($data, function (User $user, string $password) {
            $user->forceFill(['password' => $password])->save();
            $user->setRememberToken(Str::random(60));
        });
    }
}
