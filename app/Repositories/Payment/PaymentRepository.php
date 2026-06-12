<?php

namespace App\Repositories\Payment;

use App\Models\BankTransferProof;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaymentRepository
{
    public function findByReference(string $reference): ?Payment
    {
        return Payment::with('payable')
            ->where('reference', $reference)
            ->orWhere('gateway_reference', $reference)
            ->first();
    }

    public function userPayments(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Payment::with('payable')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function pendingBankTransfers(): LengthAwarePaginator
    {
        return BankTransferProof::with(['payment.payable', 'user'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);
    }

    public function paymentForPayable(string $type, int $id): ?Payment
    {
        return Payment::where('payable_type', $type)
            ->where('payable_id', $id)
            ->latest()
            ->first();
    }
}
