<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Chargeback;
use App\Services\Chargebacks\ChargebackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorChargebackController extends Controller
{
    public function __construct(private ChargebackService $chargebacks) {}

    public function index(Request $request): View
    {
        $vendor = $request->user()->vendor;
        $filters = $request->only('status');

        return view('vendor.chargebacks.index', [
            'chargebacks' => $this->chargebacks->forVendor($vendor, $filters),
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, Chargeback $chargeback): View
    {
        $this->authorizeVendor($chargeback);

        return view('vendor.chargebacks.show', [
            'chargeback' => $chargeback->load(['payment', 'escrow', 'buyer']),
        ]);
    }

    public function contest(Request $request, Chargeback $chargeback): RedirectResponse
    {
        $this->authorizeVendor($chargeback);

        $data = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
            'files' => ['nullable', 'array', 'max:5'],
            'files.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $this->chargebacks->contest(
            $chargeback,
            $request->user(),
            $data['note'],
            $request->file('files', []),
        );

        $this->flashSuccess("Evidence submitted for chargeback {$chargeback->reference}.");

        return back();
    }

    private function authorizeVendor(Chargeback $chargeback): void
    {
        abort_unless($chargeback->vendor_id === auth()->user()->vendor?->id, 403);
    }
}
