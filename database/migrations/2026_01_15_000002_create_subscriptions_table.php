<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();                     // SUB-...
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();   // billing owner (wallet holder)
            $table->foreignId('plan_id')->constrained('subscription_plans')->restrictOnDelete();
            $table->unsignedBigInteger('price');                       // kobo, locked at subscribe time
            $table->string('billing_interval');                       // copied from plan
            $table->string('status')->default('active');              // SubscriptionStatus
            $table->boolean('auto_renew')->default(true);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();                 // access valid through this date
            $table->timestamp('last_payment_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'ends_at']);
            $table->index('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
