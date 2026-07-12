<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 40);       // webhook_received|payment_initiated|payment_verified|refund_initiated etc.
            $table->string('gateway');
            $table->string('gateway_reference', 100)->nullable();
            $table->json('payload')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->boolean('processed')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['payment_id', 'event']);
            $table->index(['gateway_reference', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
