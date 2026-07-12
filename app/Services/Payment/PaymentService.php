<?php

namespace App\Services\Payment;

use App\Actions\Payment\ApproveBankTransferAction;
use App\Actions\Payment\InitiatePaymentAction;
use App\Actions\Payment\InitiateRefundAction;
use App\Actions\Payment\VerifyPaymentAction;
use App\Models\BankTransferProof;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class PaymentService
{
    public function __construct(
        private InitiatePaymentAction $initiate,
        private VerifyPaymentAction $verify,
        private ApproveBankTransferAction $approveTransfer,
        private InitiateRefundAction $refund,
    ) {}

    public function initiatePaystackPayment(User $user, int $amountKobo, Model $payable, array $metadata = [], ?string $email = null): array
    {
        return $this->initiate->execute($user, $amountKobo, $payable, 'paystack', $metadata, $email);
    }

    public function initiateBankTransferPayment(User $user, int $amountKobo, Model $payable): array
    {
        return $this->initiate->execute($user, $amountKobo, $payable, 'bank_transfer');
    }

    public function verifyPayment(Payment $payment): Payment
    {
        return $this->verify->execute($payment);
    }

    public function verifyByReference(string $reference): ?Payment
    {
        $payment = Payment::where('reference', $reference)
            ->orWhere('gateway_reference', $reference)
            ->first();

        if (! $payment) {
            return null;
        }

        return $this->verify->execute($payment);
    }

    public function uploadBankTransferProof(Payment $payment, User $user, array $data, ?UploadedFile $file = null): BankTransferProof
    {
        $path = $file ? $file->store('bank-transfer-proofs', 'public') : null;

        return BankTransferProof::create([
            'payment_id' => $payment->id,
            'user_id' => $user->id,
            'bank_name' => $data['bank_name'],
            'account_name' => $data['account_name'],
            'amount' => to_kobo((float) $data['amount']),
            'transfer_date' => $data['transfer_date'],
            'proof_file' => $path,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function approveBankTransfer(BankTransferProof $proof, User $admin): Payment
    {
        return $this->approveTransfer->approve($proof, $admin);
    }

    public function rejectBankTransfer(BankTransferProof $proof, User $admin, string $reason): void
    {
        $this->approveTransfer->reject($proof, $admin, $reason);
    }

    public function refundPayment(Payment $payment, int $amountKobo = 0, string $reason = ''): Payment
    {
        return $this->refund->execute($payment, $amountKobo, $reason);
    }
}
