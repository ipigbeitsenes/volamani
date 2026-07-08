<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Allow platform-issued documents (no vendor): drop the FK, make the
        // column nullable, then re-add the FK as null-on-delete.
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
        });
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id')->nullable()->change();
            $table->foreign('vendor_id')->references('id')->on('vendors')->nullOnDelete();
        });

        Schema::table('documents', function (Blueprint $table) {
            // 'vendor' (a seller billing their client) or 'platform' (Volamani
            // billing one of its own users).
            $table->string('issuer')->default('vendor')->after('vendor_id');

            // Contract-of-sale e-signature capture.
            $table->string('signed_name')->nullable()->after('declined_at');
            $table->string('signed_ip', 45)->nullable()->after('signed_name');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['issuer', 'signed_name', 'signed_ip']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
        });
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedBigInteger('vendor_id')->nullable(false)->change();
            $table->foreign('vendor_id')->references('id')->on('vendors')->cascadeOnDelete();
        });
    }
};
