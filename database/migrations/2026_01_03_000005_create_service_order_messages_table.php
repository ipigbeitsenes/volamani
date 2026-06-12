<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users');
            $table->text('message')->nullable();
            $table->string('attachment')->nullable();
            $table->string('attachment_name')->nullable();
            $table->boolean('is_delivery')->default(false);
            $table->boolean('is_system')->default(false);
            $table->timestamps();

            $table->index('service_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_messages');
    }
};
