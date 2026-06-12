<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\CreateVendorRequest;
use App\Services\Vendors\VendorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function __construct(private VendorService $vendorService) {}

    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()->vendor) {
            return redirect()->route('vendor.dashboard');
        }

        return view('vendor.onboarding');
    }

    public function store(CreateVendorRequest $request): RedirectResponse
    {
        if ($request->user()->vendor) {
            return redirect()->route('vendor.dashboard');
        }

        $this->vendorService->createVendor($request->user(), $request->validated());

        $this->flashSuccess('Your vendor account has been submitted for review. We\'ll notify you once approved!');

        return redirect()->route('vendor.dashboard');
    }
}
