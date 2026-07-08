<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function showRegistrationForm(Request $request): View
    {
        return view('auth.register', [
            'referralCode' => $request->query('ref') ?? $request->cookie('vlm_ref'),
        ]);
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = $this->authService->register(
            $request->validated(),
            $request->query('ref') ?? $request->cookie('vlm_ref')
        );

        // The user ticked the Terms checkbox on the form (validated 'accepted').
        $user->acceptTerms();

        Auth::login($user);

        $this->flashSuccess('Welcome to Volamani! Please verify your email to get started.');

        return redirect()->route('verification.notice');
    }
}
