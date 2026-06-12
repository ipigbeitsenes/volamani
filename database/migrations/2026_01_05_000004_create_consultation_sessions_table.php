<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('package_id')->constrained('consultation_packages');
            $table->foreignId('profile_id')->constrained('consultant_profiles');
            $table->foreignId('buyer_id')->constrained('users');
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('pending');
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('platform_fee')->default(0);
            $table->unsignedBigInteger('consultant_earnings')->default(0);
            $table->string('payment_reference')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->unsignedSmallInteger('duration_minutes');
            $table->string('meeting_link')->nullable();
            $table->string('meeting_platform')->nullable();
            $table->text('notes')->nullable();
            $table->text('consultant_notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['buyer_id', 'status']);
            $table->index(['profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_sessions');
    }
};
