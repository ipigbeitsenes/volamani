<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->boolean('is_verified_purchase')->default(false)->after('is_approved');
            $table->unsignedInteger('helpful_count')->default(0)->after('is_verified_purchase');
            $table->text('response')->nullable()->after('helpful_count');     // vendor reply
            $table->timestamp('responded_at')->nullable()->after('response');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['is_verified_purchase', 'helpful_count', 'response', 'responded_at']);
        });
    }
};
