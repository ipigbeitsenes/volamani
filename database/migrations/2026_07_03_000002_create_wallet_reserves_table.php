<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Each row is a slice of a vendor's escrow release held back as a
        // rolling chargeback reserve. It is released to spendable balance after
        // `release_at`, or clawed back if a chargeback lands first.
        Schema::create('wallet_reserves', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();               // RSV-...
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('escrow_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedBigInteger('amount');                // kobo held in reserve
            $table->string('status')->default('held');           // held / released / clawed_back

            $table->timestamp('release_at');                     // when it becomes spendable
            $table->timestamp('released_at')->nullable();
            $table->timestamp('clawed_back_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('vendor_id');
            $table->index(['status', 'release_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_reserves');
    }
};
