<?php

namespace App\Http\Controllers\Disputes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Disputes\AddDisputeMessageRequest;
use App\Http\Requests\Disputes\OpenDisputeRequest;
use App\Models\Dispute;
use App\Models\Escrow;
use App\Models\User;
use App\Notifications\SupportTicketOpenedNotification;
use App\Services\Disputes\DisputeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class DisputeController extends Controller
{
    public function __construct(private DisputeService $disputeService) {}

    public function index(): View
    {
        $disputes = $this->disputeService->forUser(auth()->user());

        return view('marketplace.disputes.index', compact('disputes'));
    }

    public function create(Escrow $escrow): View|RedirectResponse
    {
        $this->authorizeEscrowParty($escrow);

        if ($escrow->isProductEscrow() && ! $escrow->canRaiseTicket()) {
            $this->flashError('The 24-hour window to open a support ticket for this purchase has closed.');

            return redirect()->route('escrows.show', $escrow);
        }

        abort_unless($escrow->canDispute(), 422, 'These funds can no longer be disputed.');

        $escrow->load('escrowable');

        return view('marketplace.disputes.create', compact('escrow'));
    }

    public function store(OpenDisputeRequest $request, Escrow $escrow): RedirectResponse
    {
        $this->authorizeEscrowParty($escrow);

        $dispute = $this->disputeService->open(
            $escrow,
            auth()->user(),
            $request->validated(),
            $request->file('attachment')
        );

        $this->notifySupportAndVendor($dispute);

        $this->flashSuccess('Support ticket opened. The funds are now held until our team resolves this.');

        return redirect()->route('disputes.show', $dispute);
    }

    /** Alert the vendor (to respond) and the support team (to triage). */
    private function notifySupportAndVendor(Dispute $dispute): void
    {
        $recipients = User::role('admin')->get();

        if ($vendorUser = $dispute->vendor?->user) {
            $recipients->push($vendorUser);
        }

        Notification::send(
            $recipients->unique('id'),
            new SupportTicketOpenedNotification($dispute)
        );
    }

    public function show(Dispute $dispute): View
    {
        $this->authorizeParty($dispute);
        $dispute->load(['escrow.escrowable', 'buyer', 'vendor', 'resolvedBy', 'messages.sender']);

        return view('marketplace.disputes.show', compact('dispute'));
    }

    public function addMessage(AddDisputeMessageRequest $request, Dispute $dispute): RedirectResponse
    {
        $this->authorizeParty($dispute);

        $this->disputeService->addMessage(
            $dispute,
            auth()->user(),
            $request->validated()['message'],
            $request->file('attachment')
        );

        return redirect()->route('disputes.show', $dispute);
    }

    private function authorizeEscrowParty(Escrow $escrow): void
    {
        abort_unless(
            $escrow->buyer_id === auth()->id() || $escrow->vendor?->user_id === auth()->id(),
            403
        );
    }

    private function authorizeParty(Dispute $dispute): void
    {
        abort_unless($dispute->involves(auth()->user()), 403);
    }
}
