<?php

namespace App\Http\Controllers\Escrow;

use App\Http\Controllers\Controller;
use App\Models\Escrow;
use App\Services\Escrow\EscrowService;
use Illuminate\View\View;

class EscrowController extends Controller
{
    public function __construct(private EscrowService $escrowService) {}

    public function index(): View
    {
        $escrows = $this->escrowService->buyerEscrows(auth()->user());

        return view('marketplace.escrows.index', compact('escrows'));
    }

    public function show(Escrow $escrow): View
    {
        $this->authorizeBuyer($escrow);
        $escrow->load(['vendor', 'escrowable', 'transactions.actor']);

        return view('marketplace.escrows.show', compact('escrow'));
    }

    private function authorizeBuyer(Escrow $escrow): void
    {
        abort_unless($escrow->buyer_id === auth()->id(), 403);
    }
}
