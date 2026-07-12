<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ChargebackStatus;
use App\Http\Controllers\Controller;
use App\Models\Chargeback;
use App\Services\Chargebacks\ChargebackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ChargebackController extends Controller
{
    public function __construct(private ChargebackService $chargebacks) {}

    public function index(Request $request): View
    {
        $filters = $request->only('status', 'search');

        return view('admin.chargebacks.index', [
            'chargebacks' => $this->chargebacks->allForAdmin($filters),
            'filters' => $filters,
        ]);
    }

    public function show(Chargeback $chargeback): View
    {
        return view('admin.chargebacks.show', [
            'chargeback' => $chargeback->load(['payment', 'escrow', 'buyer', 'vendor', 'resolvedBy']),
        ]);
    }

    public function resolve(Request $request, Chargeback $chargeback): RedirectResponse
    {
        $data = $request->validate([
            'outcome' => ['required', Rule::in([ChargebackStatus::Won->value, ChargebackStatus::Lost->value])],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->chargebacks->resolve(
            $chargeback,
            ChargebackStatus::from($data['outcome']),
            $request->user(),
            $data['note'] ?? null,
        );

        $this->flashSuccess("Chargeback {$chargeback->reference} marked {$data['outcome']}.");

        return back();
    }
}
