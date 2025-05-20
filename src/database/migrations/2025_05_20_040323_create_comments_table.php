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
        Schema::create('comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ticket_id')->constrained('tickets','id')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users','id')->cascadeOnDelete();
            $table->text('body');
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
            $table->string('message_id')->nullable();
            $table->string('in_reply_to')->nullable();
            $table->string('references')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
