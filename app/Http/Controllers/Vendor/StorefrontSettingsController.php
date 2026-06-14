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

        // Shipping amounts are entered in naira; persist in kobo.
        $data['shipping_fee'] = isset($data['shipping_fee']) ? to_kobo($data['shipping_fee']) : 0;
        $data['free_shipping_threshold'] = (isset($data['free_shipping_threshold']) && $data['free_shipping_threshold'] !== null && $data['free_shipping_threshold'] !== '')
            ? to_kobo($data['free_shipping_threshold'])
            : null;

        $this->vendorService->updateStorefront(
            $vendor,
            $data,
            $request->file('logo'),
            $request->file('banner'),
        );

        $this->flashSuccess('Storefront updated successfully.');

        return back();
    }

    /** Quick logo/banner update (e.g. from the vendor dashboard). */
    public function updateBranding(Request $request): RedirectResponse
    {
        $request->validate([
            'logo'   => ['nullable', 'image', 'max:2048'],
            'banner' => ['nullable', 'image', 'max:5120'],
        ]);

        if (! $request->hasFile('logo') && ! $request->hasFile('banner')) {
            return back()->with('error', 'Choose a logo or banner image to upload.');
        }

        $this->vendorService->updateStorefront(
            $request->user()->vendor,
            [],
            $request->file('logo'),
            $request->file('banner'),
        );

        $this->flashSuccess('Store branding updated.');

        return back();
    }
}
