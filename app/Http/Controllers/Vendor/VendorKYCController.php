<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\KYC\SubmitKYCRequest;
use App\Services\KYC\KYCService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VendorKYCController extends Controller
{
    public function __construct(private KYCService $kycService) {}

    public function index(): View
    {
        $kyc = auth()->user()->kycVerification;

        return view('vendor.kyc.index', compact('kyc'));
    }

    public function submit(SubmitKYCRequest $request): RedirectResponse
    {
        $this->kycService->submit(auth()->user(), $request->kycData(), $request->kycFiles());

        $this->flashSuccess('Verification submitted. Approval unlocks withdrawals and verified status.');

        return redirect()->route('vendor.kyc.index');
    }
}
