<?php

namespace App\Http\Controllers\Support;

use App\Enums\ReturnStatus;
use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Services\Returns\ReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportReturnController extends Controller
{
    public function __construct(private ReturnService $returns) {}

    public function index(Request $request): View
    {
        $filters = $request->only('status', 'search');

        return view('support.returns.index', [
            'returns'  => $this->returns->allForAdmin($filters),
            'filters'  => $filters,
            'statuses' => ReturnStatus::cases(),
        ]);
    }

    public function approve(Request $request, ReturnRequest $return): RedirectResponse
    {
        $data = $request->validate(['decision_note' => ['nullable', 'string', 'max:500']]);
        $this->returns->approve($return, $request->user(), $data['decision_note'] ?? null);
        $this->flashSuccess("Return {$return->reference} approved.");

        return back();
    }

    public function reject(Request $request, ReturnRequest $return): RedirectResponse
    {
        $data = $request->validate(['decision_note' => ['required', 'string', 'max:500']]);
        $this->returns->reject($return, $request->user(), $data['decision_note']);
        $this->flashWarning("Return {$return->reference} declined.");

        return back();
    }

    public function confirm(ReturnRequest $return): RedirectResponse
    {
        $this->returns->confirm($return, auth()->user());
        $this->flashSuccess("Return {$return->reference} completed — buyer refunded.");

        return back();
    }
}
