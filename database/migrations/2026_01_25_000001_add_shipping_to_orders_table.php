<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Physical orders require shipping + delivery-confirmed escrow release.
            $table->boolean('requires_shipping')->default(false)->after('payment_status');
            $table->unsignedBigInteger('shipping_fee')->default(0)->after('vendor_earnings'); // kobo

            // Delivery address snapshot (captured at checkout).
            $table->string('ship_to_name')->nullable()->after('shipping_fee');
            $table->string('ship_to_phone', 30)->nullable()->after('ship_to_name');
            $table->string('ship_to_address')->nullable()->after('ship_to_phone');
            $table->string('ship_to_city', 80)->nullable()->after('ship_to_address');
            $table->string('ship_to_state', 80)->nullable()->after('ship_to_city');

            // Fulfillment tracking
            $table->string('tracking_number', 120)->nullable()->after('ship_to_state');
            $table->string('courier', 120)->nullable()->after('tracking_number');
            $table->timestamp('shipped_at')->nullable()->after('paid_at');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');

            $table->index('requires_shipping');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('variant_id')->nullable()->after('product_id')
                ->constrained('product_variants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('variant_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'requires_shipping', 'shipping_fee', 'ship_to_name', 'ship_to_phone',
                'ship_to_address', 'ship_to_city', 'ship_to_state',
                'tracking_number', 'courier', 'shipped_at', 'delivered_at',
            ]);
        });
    }
};
