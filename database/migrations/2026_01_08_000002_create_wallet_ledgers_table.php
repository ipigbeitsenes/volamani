<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('type');                              // TransactionType enum
            $table->unsignedBigInteger('amount');                // always positive (kobo)
            $table->unsignedBigInteger('balance_after');         // wallet balance snapshot after entry
            $table->string('description');
            $table->json('metadata')->nullable();
            $table->nullableMorphs('ledgerable');                // Payment, Order, ServiceOrder, WalletWithdrawal, etc.
            $table->timestamp('created_at')->useCurrent();       // immutable — no updated_at

            $table->index(['wallet_id', 'created_at']);
            // nullableMorphs('ledgerable') already indexes (ledgerable_type, ledgerable_id).
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_ledgers');
    }
};
