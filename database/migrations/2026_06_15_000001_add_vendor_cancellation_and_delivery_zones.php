<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Why an order was cancelled and who cancelled it (vendor user).
            $table->text('cancellation_reason')->nullable()->after('notes');
            $table->foreignId('cancelled_by')->nullable()->after('cancellation_reason')
                ->constrained('users')->nullOnDelete();
        });

        Schema::table('vendors', function (Blueprint $table) {
            // States / cities the vendor will NOT deliver to (arrays of names).
            $table->json('no_delivery_states')->nullable()->after('ships_to');
            $table->json('no_delivery_cities')->nullable()->after('no_delivery_states');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn('cancellation_reason');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['no_delivery_states', 'no_delivery_cities']);
        });
    }
};
