<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\UpdateStorefrontRequest;
use App\Services\Vendors\VendorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontSettingsController extends Controller
{
    public function __construct(private VendorService $vendorService) {}

    public function index(Request $request): View
    {
        return view('vendor.storefront-settings', [
            'vendor' => $request->user()->vendor,
        ]);
    }

    public function update(UpdateStorefrontRequest $request): RedirectResponse
    {
        $vendor = $request->user()->vendor;
        $data   = $request->safe()->except(['logo', 'banner']);

        $this->vendorService->updateStorefront(
            $vendor,
            $data,
            $request->file('logo'),
            $request->file('banner'),
        );

        $this->flashSuccess('Storefront updated successfully.');

        return back();
    }
}
