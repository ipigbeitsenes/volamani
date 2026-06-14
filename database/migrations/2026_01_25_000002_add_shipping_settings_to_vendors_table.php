<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            // Flat per-vendor shipping (Phase 3 v1 shipping model).
            $table->unsignedBigInteger('shipping_fee')->default(0)->after('plan');        // kobo
            $table->unsignedBigInteger('free_shipping_threshold')->nullable()->after('shipping_fee'); // kobo; null = never free
            $table->string('ships_to')->nullable()->after('free_shipping_threshold');     // free-text note
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['shipping_fee', 'free_shipping_threshold', 'ships_to']);
        });
    }
};
