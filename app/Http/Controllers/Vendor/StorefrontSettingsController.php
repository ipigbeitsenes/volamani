<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\UpdateStorefrontRequest;
use App\Models\Vendor;
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
        $data = $request->safe()->except(['logo', 'banner']);

        // Shipping amounts are entered in the vendor's currency; persist as the
        // base equivalent (in minor units), like product prices.
        $currency = $data['currency'] ?? ($vendor instanceof Vendor ? $vendor->currencyCode() : currency()->base());
        $data['shipping_fee'] = isset($data['shipping_fee']) ? currency()->toBase(to_kobo($data['shipping_fee']), $currency) : 0;
        $data['free_shipping_threshold'] = (isset($data['free_shipping_threshold']) && $data['free_shipping_threshold'] !== null && $data['free_shipping_threshold'] !== '')
            ? currency()->toBase(to_kobo($data['free_shipping_threshold']), $currency)
            : null;

        // Delivery-exclusion zones: states/regions and cities both arrive as
        // comma-separated free-text fields. Store both as clean string arrays.
        $splitToArray = fn (?string $csv) => collect(explode(',', $csv ?? ''))
            ->map(fn ($v) => trim($v))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $data['no_delivery_states'] = $splitToArray($data['no_delivery_states'] ?? '');
        $data['no_delivery_cities'] = $splitToArray($data['no_delivery_cities'] ?? '');

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
            'logo' => ['nullable', 'image', 'max:5120'],
            'banner' => ['nullable', 'image', 'max:10240'],
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
