<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Escrow;
use App\Services\Escrow\EscrowService;
use Illuminate\View\View;

class VendorEscrowController extends Controller
{
    public function __construct(private EscrowService $escrowService) {}

    public function index(): View
    {
        $vendor   = auth()->user()->vendor;
        $escrows  = $this->escrowService->vendorEscrows($vendor);
        $heldTotal = $this->escrowService->heldTotalForVendor($vendor);

        return view('vendor.escrows.index', compact('escrows', 'heldTotal'));
    }

    public function show(Escrow $escrow): View
    {
        abort_unless($escrow->vendor_id === auth()->user()->vendor?->id, 403);
        $escrow->load(['buyer', 'escrowable', 'transactions.actor']);

        return view('vendor.escrows.show', compact('escrow'));
    }
}
