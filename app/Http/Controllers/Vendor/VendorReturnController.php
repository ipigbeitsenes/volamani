<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Services\Returns\ReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorReturnController extends Controller
{
    public function __construct(private ReturnService $returns) {}

    public function index(Request $request): View
    {
        $vendor = $request->user()->vendor;
        $filters = $request->only('status');

        return view('vendor.returns.index', [
            'returns' => $this->returns->forVendor($vendor, $filters),
            'filters' => $filters,
        ]);
    }

    public function approve(Request $request, ReturnRequest $return): RedirectResponse
    {
        $this->authorizeVendor($return);
        $data = $request->validate(['decision_note' => ['nullable', 'string', 'max:500']]);

        $this->returns->approve($return, $request->user(), $data['decision_note'] ?? null);
        $this->flashSuccess("Return {$return->reference} approved. The buyer will ship the item back.");

        return back();
    }

    public function reject(Request $request, ReturnRequest $return): RedirectResponse
    {
        $this->authorizeVendor($return);
        $data = $request->validate(['decision_note' => ['required', 'string', 'max:500']]);

        $this->returns->reject($return, $request->user(), $data['decision_note']);
        $this->flashWarning("Return {$return->reference} declined.");

        return back();
    }

    public function confirm(ReturnRequest $return): RedirectResponse
    {
        $this->authorizeVendor($return);

        $this->returns->confirm($return, auth()->user());
        $this->flashSuccess("Return {$return->reference} completed — the buyer has been refunded and stock restored.");

        return back();
    }

    private function authorizeVendor(ReturnRequest $return): void
    {
        abort_unless($return->vendor_id === auth()->user()->vendor?->id, 403);
    }
}
