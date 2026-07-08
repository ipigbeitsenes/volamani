<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('token', 40)->unique();                 // public/guest identifier (localStorage)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();   // logged-in visitor
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('subject')->nullable();
            $table->string('status')->default('open');             // ChatConversationStatus
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // agent
            $table->timestamp('last_visitor_at')->nullable();      // drives the bot fallback timer
            $table->timestamp('last_agent_at')->nullable();
            $table->boolean('bot_replied')->default(false);        // offline bot has already fired once
            $table->unsignedInteger('agent_unread')->default(0);   // visitor messages the team hasn't opened
            $table->unsignedInteger('visitor_unread')->default(0); // agent/bot messages the visitor hasn't seen
            $table->timestamps();

            $table->index(['status', 'last_visitor_at']);
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // sender (null for guest/bot/system)
            $table->string('sender_type');                         // ChatSenderType: visitor|agent|bot|system
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['chat_conversation_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
    }
};
