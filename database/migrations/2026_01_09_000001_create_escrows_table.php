<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escrows', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();

            // What the escrow is securing (Order / ServiceOrder / ConsultationSession)
            $table->morphs('escrowable');

            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            // Vendor's wallet that holds the pending earnings
            $table->foreignId('wallet_id')->nullable()->constrained('wallets')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();

            // All amounts in kobo. total = vendor_earnings + platform_fee
            $table->unsignedBigInteger('total_amount');
            $table->unsignedBigInteger('platform_fee')->default(0);
            $table->unsignedBigInteger('vendor_earnings');
            $table->unsignedBigInteger('released_amount')->default(0);
            $table->unsignedBigInteger('refunded_amount')->default(0);

            $table->string('status')->default('holding')->index();
            $table->text('notes')->nullable();

            // When the funds may auto-release to the vendor (buyer-protection window)
            $table->timestamp('auto_release_at')->nullable()->index();
            $table->timestamp('held_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('disputed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escrows');
    }
};
