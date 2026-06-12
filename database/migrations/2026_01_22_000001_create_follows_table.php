<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['follower_id', 'vendor_id']);
            $table->index(['vendor_id', 'created_at']);
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->unsignedInteger('followers_count')->default(0)->after('reviews_count');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('followers_count');
        });

        Schema::dropIfExists('follows');
    }
};
