<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\KYCVerification;
use App\Services\KYC\KYCService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupportKYCController extends Controller
{
    private const DOCUMENT_FIELDS = ['document_front', 'document_back', 'selfie', 'proof_of_address'];

    public function __construct(private KYCService $kycService) {}

    public function index(): View
    {
        $filters       = request()->only(['status', 'search']);
        $verifications = $this->kycService->forAdmin(20, $filters);

        return view('support.kyc.index', compact('verifications', 'filters'));
    }

    public function show(KYCVerification $kyc): View
    {
        $kyc->load(['user', 'reviewedBy']);

        return view('support.kyc.show', compact('kyc'));
    }

    public function approve(KYCVerification $kyc): RedirectResponse
    {
        $this->kycService->approve($kyc, auth()->user());
        $this->flashSuccess("KYC {$kyc->reference} approved — user is now verified.");

        return redirect()->route('support.kyc.show', $kyc);
    }

    public function reject(KYCVerification $kyc): RedirectResponse
    {
        $reason = request()->validate(['reason' => ['required', 'string', 'max:1000']])['reason'];

        $this->kycService->reject($kyc, auth()->user(), $reason);
        $this->flashWarning("KYC {$kyc->reference} rejected. The user has been notified to resubmit.");

        return redirect()->route('support.kyc.show', $kyc);
    }

    /** Securely stream a private identity document for review. */
    public function document(KYCVerification $kyc, string $field): StreamedResponse
    {
        abort_unless(in_array($field, self::DOCUMENT_FIELDS, true), 404);

        $path = $kyc->{$field};
        abort_unless($path && Storage::disk('private')->exists($path), 404);

        return Storage::disk('private')->response($path);
    }
}
