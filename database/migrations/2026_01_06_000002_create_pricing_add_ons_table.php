<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_add_ons', function (Blueprint $table) {
            $table->id();
            $table->string('category')->nullable();  // null = applies to all categories
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('price')->default(0);  // kobo
            $table->boolean('is_percentage')->default(false); // if true, price = basis points (e.g. 1000 = 10%)
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_add_ons');
    }
};
