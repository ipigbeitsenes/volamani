<?php

namespace App\Http\Controllers\KYC;

use App\Http\Controllers\Controller;
use App\Http\Requests\KYC\SubmitKYCRequest;
use App\Services\KYC\KYCService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KYCController extends Controller
{
    public function __construct(private KYCService $kycService) {}

    public function index(): View
    {
        $kyc = auth()->user()->kycVerification;

        return view('marketplace.kyc.index', compact('kyc'));
    }

    public function submit(SubmitKYCRequest $request): RedirectResponse
    {
        $this->kycService->submit(auth()->user(), $request->kycData(), $request->kycFiles());

        $this->flashSuccess('Identity verification submitted. We will review it within 1–2 business days.');

        return redirect()->route('kyc.index');
    }
}
