<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AffiliateStatus;
use App\Http\Controllers\Controller;
use App\Models\AffiliateAccount;
use App\Models\AffiliateCommission;
use App\Services\Affiliate\AffiliateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AffiliateController extends Controller
{
    public function __construct(private AffiliateService $affiliateService) {}

    public function index(Request $request): View
    {
        $filters  = $request->only('status', 'search');
        $accounts = $this->affiliateService->accountsForAdmin(20, $filters);

        $stats = [
            'pending_count'  => $this->affiliateService->pendingCommissionsCount(),
            'pending_payout' => $this->affiliateService->pendingPayoutTotal(),
            'top'            => $this->affiliateService->topAffiliates(5),
        ];

        return view('admin.affiliates.index', compact('accounts', 'filters', 'stats'));
    }

    public function show(AffiliateAccount $account): View
    {
        $account->load('user');
        $commissions = $this->affiliateService->commissionsFor($account);
        $referrals   = $account->referrals()->with('referredUser')->limit(10)->get();

        return view('admin.affiliates.show', compact('account', 'commissions', 'referrals'));
    }

    public function commissions(Request $request): View
    {
        $filters     = $request->only('status', 'type');
        $commissions = $this->affiliateService->commissionsForAdmin(25, $filters);

        return view('admin.affiliates.commissions', compact('commissions', 'filters'));
    }

    public function approve(AffiliateCommission $commission): RedirectResponse
    {
        $this->affiliateService->approveCommission($commission);

        $this->flashSuccess("Commission {$commission->reference} approved and paid out.");

        return back();
    }

    public function cancel(Request $request, AffiliateCommission $commission): RedirectResponse
    {
        $this->affiliateService->cancelCommission($commission, $request->input('reason'));

        $this->flashSuccess("Commission {$commission->reference} cancelled.");

        return back();
    }

    public function suspend(AffiliateAccount $account): RedirectResponse
    {
        $this->affiliateService->setStatus($account, AffiliateStatus::Suspended);

        $this->flashSuccess("Affiliate account for {$account->user->name} suspended.");

        return back();
    }

    public function activate(AffiliateAccount $account): RedirectResponse
    {
        $this->affiliateService->setStatus($account, AffiliateStatus::Active);

        $this->flashSuccess("Affiliate account for {$account->user->name} reactivated.");

        return back();
    }
}
