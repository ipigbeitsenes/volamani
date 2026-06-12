<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('service_id')->constrained('freelance_services');
            $table->foreignId('package_id')->constrained('service_packages');
            $table->foreignId('buyer_id')->constrained('users');
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('pending');
            $table->unsignedBigInteger('total_amount');
            $table->unsignedBigInteger('platform_fee')->default(0);
            $table->unsignedBigInteger('vendor_earnings')->default(0);
            $table->string('payment_reference')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('requirements')->nullable();
            $table->unsignedTinyInteger('revisions_allowed')->default(1);
            $table->unsignedTinyInteger('revisions_used')->default(0);
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['buyer_id', 'status']);
            $table->index(['vendor_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
