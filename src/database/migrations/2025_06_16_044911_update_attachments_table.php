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
        Schema::table("attachments", function (Blueprint $table) {
            $table->foreignUuid('email_id')->nullable()->references('id')->on('ticket_emails')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("attachments", function (Blueprint $table) {
            $table->dropForeign(['email_id']);
            $table->dropColumn('email_id');
        });
    }
};
