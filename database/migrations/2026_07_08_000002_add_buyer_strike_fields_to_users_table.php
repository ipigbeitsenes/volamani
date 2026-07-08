<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Running count of active abuse strikes + the derived flag/suspend state.
        // Detailed history lives in buyer_strikes.
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('buyer_strikes')->default(0)->after('kyc_status');
            $table->timestamp('buyer_strikes_updated_at')->nullable()->after('buyer_strikes');
            $table->boolean('buyer_flagged')->default(false)->after('buyer_strikes_updated_at');
            $table->timestamp('buyer_flagged_at')->nullable()->after('buyer_flagged');
            $table->boolean('purchases_suspended')->default(false)->after('buyer_flagged_at');
            $table->timestamp('purchases_suspended_at')->nullable()->after('purchases_suspended');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'buyer_strikes', 'buyer_strikes_updated_at', 'buyer_flagged',
                'buyer_flagged_at', 'purchases_suspended', 'purchases_suspended_at',
            ]);
        });
    }
};
