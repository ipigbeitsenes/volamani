<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Wallet\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class WalletLedgerTest extends TestCase
{
    use RefreshDatabase;

    private function wallet(int $balance = 0): Wallet
    {
        return Wallet::create([
            'user_id' => User::factory()->create()->id,
            'balance' => $balance,
            'escrow_balance' => 0,
        ]);
    }

    public function test_credit_increases_balance_and_writes_a_ledger_entry(): void
    {
        $wallet = $this->wallet();

        app(WalletService::class)->credit($wallet, 50_000, TransactionType::Credit, 'Test credit');

        $wallet->refresh();
        $this->assertSame(50_000, (int) $wallet->balance);
        $this->assertCount(1, $wallet->ledgers);
        $this->assertSame(50_000, (int) $wallet->ledgers->first()->balance_after);
    }

    public function test_debit_decreases_balance_and_records_balance_after(): void
    {
        $wallet = $this->wallet(100_000);

        app(WalletService::class)->debit($wallet, 30_000, TransactionType::Debit, 'Test debit');

        $wallet->refresh();
        $this->assertSame(70_000, (int) $wallet->balance);
        $this->assertSame(70_000, (int) $wallet->ledgers->first()->balance_after);
    }

    public function test_debit_beyond_available_balance_is_rejected(): void
    {
        $wallet = $this->wallet(10_000);

        $this->expectException(HttpException::class);

        app(WalletService::class)->debit($wallet, 20_000, TransactionType::Debit, 'Overdraft attempt');
    }

    public function test_frozen_wallet_cannot_be_debited(): void
    {
        $wallet = $this->wallet(100_000);
        $wallet->update(['is_frozen' => true]);

        $this->expectException(HttpException::class);

        app(WalletService::class)->debit($wallet, 10_000, TransactionType::Debit, 'Frozen debit');
    }

    public function test_escrow_increment_and_decrement_never_touch_spendable_balance(): void
    {
        $wallet = $this->wallet(40_000);
        $service = app(WalletService::class);

        $service->incrementEscrow($wallet, 25_000);
        $wallet->refresh();
        $this->assertSame(25_000, (int) $wallet->escrow_balance);
        $this->assertSame(40_000, (int) $wallet->balance);
        // Escrow holds must NOT write wallet ledger rows (reconciliation rule).
        $this->assertCount(0, $wallet->ledgers);

        $service->decrementEscrow($wallet, 25_000);
        $wallet->refresh();
        $this->assertSame(0, (int) $wallet->escrow_balance);
        $this->assertSame(40_000, (int) $wallet->balance);
    }
}
