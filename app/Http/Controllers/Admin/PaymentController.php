<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Admin\AdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private AdminService $admin) {}

    public function index(Request $request): View
    {
        $filters  = $request->only('status', 'gateway', 'search');
        $payments = $this->admin->payments($filters);

        return view('admin.payments.index', compact('payments', 'filters'));
    }

    public function show(Payment $payment): View
    {
        $payment->load(['user', 'payable', 'logs', 'bankTransferProof']);

        return view('admin.payments.show', compact('payment'));
    }

    public function approveOffline(Payment $payment): RedirectResponse
    {
        if ($this->admin->approveOfflinePayment($payment, auth()->user())) {
            $this->flashSuccess('Bank transfer approved and order fulfilled.');
        } else {
            $this->flashError('No pending bank-transfer proof found for this payment.');
        }

        return back();
    }
}
