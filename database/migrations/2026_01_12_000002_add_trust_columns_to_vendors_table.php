<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('average_rating', 3, 2)->default(0.00)->after('verified_at');
            $table->unsignedInteger('reviews_count')->default(0)->after('average_rating');
            $table->unsignedTinyInteger('trust_score')->default(0)->after('reviews_count'); // 0–100
            $table->index('average_rating');
            $table->index('trust_score');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['average_rating', 'reviews_count', 'trust_score']);
        });
    }
};
