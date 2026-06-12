<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Escrow;
use App\Services\Escrow\EscrowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EscrowController extends Controller
{
    public function __construct(private EscrowService $escrowService) {}

    public function index(): View
    {
        $filters = request()->only(['status', 'search']);
        $escrows = $this->escrowService->allForAdmin(20, $filters);

        return view('admin.escrows.index', compact('escrows', 'filters'));
    }

    public function show(Escrow $escrow): View
    {
        $escrow->load(['buyer', 'vendor', 'payment', 'escrowable', 'transactions.actor']);

        return view('admin.escrows.show', compact('escrow'));
    }

    public function release(Escrow $escrow): RedirectResponse
    {
        $this->escrowService->release($escrow, null, auth()->user());
        $this->flashSuccess("Escrow {$escrow->reference} released to the vendor.");

        return redirect()->route('admin.escrows.show', $escrow);
    }

    public function refund(Escrow $escrow): RedirectResponse
    {
        $reason = request()->validate(['reason' => ['nullable', 'string', 'max:1000']])['reason'] ?? null;

        $this->escrowService->refund($escrow, auth()->user(), $reason);
        $this->flashSuccess("Escrow {$escrow->reference} refunded to the buyer's wallet.");

        return redirect()->route('admin.escrows.show', $escrow);
    }
}
