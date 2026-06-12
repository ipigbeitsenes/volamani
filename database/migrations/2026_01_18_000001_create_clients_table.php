<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // linked Volamani account
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->text('address')->nullable();
            $table->string('status')->default('lead');        // ClientStatus
            $table->string('source')->default('manual');      // ClientSource
            $table->json('tags')->nullable();
            $table->text('about')->nullable();
            $table->unsignedBigInteger('total_spent')->default(0);  // kobo, aggregated
            $table->unsignedInteger('orders_count')->default(0);
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['vendor_id', 'user_id']);
            $table->index(['vendor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
