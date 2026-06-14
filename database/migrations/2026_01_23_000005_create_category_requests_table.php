<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vendors can request a custom category; admins approve (which inserts the
        // category into the relevant domain tree) or reject.
        Schema::create('category_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('domain', 20);                  // digital | physical | service
            $table->string('name');
            $table->unsignedBigInteger('parent_id')->nullable(); // suggested parent in that domain's tree
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            // Set once approved — the category row created in the domain tree.
            $table->unsignedBigInteger('created_category_id')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('domain');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_requests');
    }
};
