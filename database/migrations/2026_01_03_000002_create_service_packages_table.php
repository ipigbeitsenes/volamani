<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('freelance_services')->cascadeOnDelete();
            $table->string('tier');
            $table->string('name');
            $table->text('description');
            $table->unsignedBigInteger('price');
            $table->unsignedTinyInteger('delivery_days');
            $table->unsignedTinyInteger('revisions')->default(1);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['service_id', 'tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_packages');
    }
};
