<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Admin\AdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancePaymentController extends Controller
{
    public function __construct(private AdminService $admin) {}

    public function index(Request $request): View
    {
        $filters = $request->only('status', 'gateway', 'search');
        $payments = $this->admin->payments($filters, 20);

        return view('finance.payments.index', compact('payments', 'filters'));
    }

    /** Approve a pending bank-transfer (offline) payment. */
    public function approveOffline(Payment $payment): RedirectResponse
    {
        if ($this->admin->approveOfflinePayment($payment, auth()->user())) {
            $this->flashSuccess("Payment {$payment->reference} approved and fulfilled.");
        } else {
            $this->flashError('No pending bank-transfer proof found for this payment.');
        }

        return back();
    }
}
