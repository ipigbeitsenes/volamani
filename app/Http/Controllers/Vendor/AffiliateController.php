<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\Affiliate\AffiliateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AffiliateController extends Controller
{
    public function __construct(private AffiliateService $affiliateService) {}

    public function index(): View
    {
        $account = $this->affiliateService->accountFor(auth()->user());

        $recentCommissions = $account
            ? $account->commissions()->with('buyer')->limit(5)->get()
            : collect();

        $recentReferrals = $account
            ? $account->referrals()->with('referredUser')->limit(5)->get()
            : collect();

        return view('vendor.affiliates.index', compact('account', 'recentCommissions', 'recentReferrals'));
    }

    public function enroll(): RedirectResponse
    {
        if (! settings('affiliate_enabled', true)) {
            $this->flashError('The affiliate program is currently unavailable.');

            return redirect()->route('vendor.affiliates.index');
        }

        $this->affiliateService->enroll(auth()->user());

        $this->flashSuccess('You have joined the affiliate program. Start sharing your link to earn.');

        return redirect()->route('vendor.affiliates.index');
    }

    public function commissions(): View
    {
        $account = $this->requireAccount();
        $commissions = $this->affiliateService->commissionsFor($account);

        return view('vendor.affiliates.commissions', compact('account', 'commissions'));
    }

    public function referrals(): View
    {
        $account = $this->requireAccount();
        $referrals = $this->affiliateService->referralsFor($account);

        return view('vendor.affiliates.referrals', compact('account', 'referrals'));
    }

    private function requireAccount()
    {
        $account = $this->affiliateService->accountFor(auth()->user());

        abort_unless($account !== null, 403, 'You have not joined the affiliate program yet.');

        return $account;
    }
}
