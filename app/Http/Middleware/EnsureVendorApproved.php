<?php

namespace App\Http\Middleware;

use Closure;
use App\Enums\Status;
use Illuminate\Http\Request;

class EnsureVendorApproved
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! $user->vendor) {
            return redirect()->route('vendor.onboarding')
                ->with('error', 'You need to set up a vendor account first.');
        }

        if ($user->vendor->status !== Status::Active) {
            return redirect()->route('dashboard')
                ->with('error', 'Your vendor account is pending approval or has been suspended.');
        }

        return $next($request);
    }
}
