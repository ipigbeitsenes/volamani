<?php

namespace App\Http\Middleware;

use Closure;
use App\Enums\KYCStatus;
use Illuminate\Http\Request;

class EnsureKYCVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || $user->kyc_status !== KYCStatus::Verified) {
            return redirect()->route('kyc.index')
                ->with('error', 'Please complete identity verification to access this feature.');
        }

        return $next($request);
    }
}
