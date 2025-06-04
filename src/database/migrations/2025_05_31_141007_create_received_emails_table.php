<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('received_emails', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('message_id')->unique();
            $table->string('in_reply_to')->nullable();
            $table->string('from_email');
            $table->string('to_email');
            $table->string('subject');
            $table->longText('body');
            $table->json('attachments')->nullable();
            $table->enum('type', ['new_ticket', 'reply', 'feedback', 'unknown']);
            $table->timestamp('received_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('received_emails');
    }
};
