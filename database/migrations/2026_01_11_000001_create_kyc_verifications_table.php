<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            $table->string('type')->default('individual');   // KYCType
            $table->string('status')->default('pending')->index(); // KYCStatus

            // Identity
            $table->string('full_name');
            $table->string('id_type');                        // KYCDocumentType
            $table->string('id_number');
            $table->date('date_of_birth')->nullable();

            // Address
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('Nigeria');

            // Business (type = business)
            $table->string('business_name')->nullable();
            $table->string('rc_number')->nullable();

            // Documents (private disk — sensitive)
            $table->string('document_front')->nullable();
            $table->string('document_back')->nullable();
            $table->string('selfie')->nullable();
            $table->string('proof_of_address')->nullable();

            // Review
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_verifications');
    }
};
