<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status')->default('active');                 // AffiliateStatus
            $table->decimal('commission_rate', 5, 2)->nullable();        // % override of settings('affiliate_commission')
            $table->unsignedInteger('clicks_count')->default(0);
            $table->unsignedInteger('signups_count')->default(0);
            $table->unsignedInteger('conversions_count')->default(0);
            $table->unsignedBigInteger('total_earned')->default(0);      // kobo, lifetime commissions earned
            $table->unsignedBigInteger('total_paid')->default(0);        // kobo, credited to wallet
            $table->timestamp('joined_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('total_earned');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_accounts');
    }
};
