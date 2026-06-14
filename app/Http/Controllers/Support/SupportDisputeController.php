<?php

namespace App\Http\Controllers\Support;

use App\Enums\DisputeResolution;
use App\Http\Controllers\Controller;
use App\Http\Requests\Disputes\AddDisputeMessageRequest;
use App\Http\Requests\Disputes\ResolveDisputeRequest;
use App\Models\Dispute;
use App\Services\Disputes\DisputeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupportDisputeController extends Controller
{
    public function __construct(private DisputeService $disputeService) {}

    public function index(): View
    {
        $filters  = request()->only(['status', 'search']);
        $disputes = $this->disputeService->allForAdmin(20, $filters);

        return view('support.disputes.index', compact('disputes', 'filters'));
    }

    public function show(Dispute $dispute): View
    {
        $dispute->load(['escrow.escrowable', 'buyer', 'vendor', 'raisedBy', 'resolvedBy', 'messages.sender']);

        return view('support.disputes.show', compact('dispute'));
    }

    public function resolve(ResolveDisputeRequest $request, Dispute $dispute): RedirectResponse
    {
        $data       = $request->validated();
        $resolution = DisputeResolution::from($data['resolution']);
        $share      = isset($data['vendor_share']) ? to_kobo((float) $data['vendor_share']) : null;

        $this->disputeService->resolve($dispute, auth()->user(), $resolution, $share, $data['note'] ?? null);

        $this->flashSuccess("Ticket {$dispute->reference} resolved — {$resolution->label()}.");

        return redirect()->route('support.disputes.show', $dispute);
    }

    public function addMessage(AddDisputeMessageRequest $request, Dispute $dispute): RedirectResponse
    {
        $this->disputeService->addMessage(
            $dispute,
            auth()->user(),
            $request->validated()['message'],
            $request->file('attachment'),
            isStaff: true
        );

        return redirect()->route('support.disputes.show', $dispute);
    }

    public function escalate(Dispute $dispute): RedirectResponse
    {
        $note = request()->validate(['note' => ['nullable', 'string', 'max:1000']])['note'] ?? null;

        $this->disputeService->escalate($dispute, auth()->user(), $note);
        $this->flashWarning("Ticket {$dispute->reference} escalated for senior review.");

        return redirect()->route('support.disputes.show', $dispute);
    }
}
