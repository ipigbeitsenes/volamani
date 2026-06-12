<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users');
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('title');
            $table->longText('description');
            $table->unsignedBigInteger('budget_min')->nullable();
            $table->unsignedBigInteger('budget_max')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->string('status')->default('open');
            $table->unsignedInteger('quotations_count')->default(0);
            $table->unsignedBigInteger('accepted_quotation_id')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->boolean('is_public')->default(true);
            $table->string('location')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'deadline_at']);
            $table->index(['buyer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_requests');
    }
};
