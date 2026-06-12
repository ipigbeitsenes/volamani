<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('type');                                     // DocumentType: invoice | quotation
            $table->string('number');                                   // INV-2026-0001 (unique per vendor)
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->text('client_address')->nullable();
            $table->string('title')->nullable();
            $table->string('status')->default('draft');                 // DocumentStatus
            $table->string('currency', 3)->default('NGN');
            $table->unsignedBigInteger('subtotal')->default(0);         // kobo
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);             // %
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedBigInteger('amount_paid')->default(0);
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();                       // invoices
            $table->date('valid_until')->nullable();                    // quotations
            $table->foreignId('converted_to_id')->nullable()->constrained('documents')->nullOnDelete(); // quotation → invoice
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['vendor_id', 'number']);
            $table->index(['vendor_id', 'type', 'status']);
            $table->index(['client_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
