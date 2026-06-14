<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_requests', function (Blueprint $table) {
            // When set, the request is sent DIRECTLY to this vendor (private —
            // only they can see and quote). Null = open public request board.
            $table->foreignId('vendor_id')->nullable()->after('buyer_id')
                ->constrained('vendors')->nullOnDelete();
            $table->index('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::table('product_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_id');
        });
    }
};
