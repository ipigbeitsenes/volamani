<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SLA tracking: the deadline for the currently-awaited response and a
        // flag marking a dispute that has been auto-escalated for missing it.
        Schema::table('disputes', function (Blueprint $table) {
            $table->timestamp('response_due_at')->nullable()->after('escalated_at');
            $table->boolean('sla_breached')->default(false)->after('response_due_at');
        });
    }

    public function down(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            $table->dropColumn(['response_due_at', 'sla_breached']);
        });
    }
};
