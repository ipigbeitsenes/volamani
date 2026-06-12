<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_request_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('product_requests')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->unsignedBigInteger('price');
            $table->unsignedTinyInteger('delivery_days');
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();

            $table->unique(['request_id', 'vendor_id']);
            $table->index(['vendor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_request_quotations');
    }
};
