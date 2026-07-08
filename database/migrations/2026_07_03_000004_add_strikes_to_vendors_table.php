<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Running count of active strikes; reaching the configured threshold
        // auto-suspends the store. Detailed history lives in vendor_strikes.
        Schema::table('vendors', function (Blueprint $table) {
            $table->unsignedInteger('strikes')->default(0)->after('trust_score');
            $table->timestamp('strikes_updated_at')->nullable()->after('strikes');
            $table->boolean('suspended_for_strikes')->default(false)->after('strikes_updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['strikes', 'strikes_updated_at', 'suspended_for_strikes']);
        });
    }
};
