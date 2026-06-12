<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();

            // Status
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('pending');

            // Amounts (in kobo)
            $table->unsignedBigInteger('total_amount');
            $table->unsignedBigInteger('platform_fee')->default(0);
            $table->unsignedBigInteger('vendor_earnings')->default(0);

            // Payment info
            $table->string('payment_reference')->nullable();
            $table->string('payment_method')->nullable(); // paystack, wallet, bank_transfer
            $table->string('currency', 3)->default('NGN');

            // Metadata
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('payment_status');
            $table->index('buyer_id');
            $table->index('vendor_id');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');           // snapshot of item name at purchase time
            $table->string('type')->default('product'); // product, service
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedBigInteger('unit_price');  // kobo
            $table->unsignedBigInteger('subtotal');    // kobo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
