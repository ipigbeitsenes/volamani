<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\UploadProofRequest;
use App\Models\Payment;
use App\Services\Payment\PaymentService;

class BankTransferController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function uploadProof(UploadProofRequest $request, Payment $payment)
    {
        abort_unless($payment->isPending(), 422, 'This payment is no longer pending.');

        $this->paymentService->uploadBankTransferProof(
            $payment,
            auth()->user(),
            $request->validated(),
            $request->file('proof_file')
        );

        return redirect()->route('checkout.pending', $payment)
            ->with('success', 'Proof uploaded! We\'ll verify your payment within 2–4 hours.');
    }
}
