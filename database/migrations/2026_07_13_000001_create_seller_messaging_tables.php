<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * In-app buyer ↔ seller messaging: one conversation thread per buyer/vendor pair
 * (with the product it started about for context) and the messages within it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->unique(['buyer_id', 'vendor_id']);
            $table->index(['vendor_id', 'last_message_at']);
            $table->index(['buyer_id', 'last_message_at']);
        });

        Schema::create('seller_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['seller_conversation_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_messages');
        Schema::dropIfExists('seller_conversations');
    }
};
