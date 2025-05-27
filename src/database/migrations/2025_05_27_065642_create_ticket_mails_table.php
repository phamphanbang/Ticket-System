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
        Schema::create('ticket_mails', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ticket_id')->constrained('tickets', 'id')->cascadeOnDelete();
            $table->string('message_id');
            $table->longText('raw_email');
            $table->longText('parse_email');
            $table->string('in_reply_to')->nullable();
            $table->json('references')->nullable();
            $table->timestamps();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['raw_email','message_id']);
            $table->foreignUuid('created_mail_id')->nullable()->constrained('ticket_mails', 'id')->nullOnDelete();

        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn(['message_id','in_reply_to','references']);
            $table->foreignUuid('mail_id')->nullable()->constrained('ticket_mails', 'id')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_mails');
    }
};
