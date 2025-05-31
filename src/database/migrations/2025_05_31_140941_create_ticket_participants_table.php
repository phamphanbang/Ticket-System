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
        Schema::create('ticket_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ticket_id')->constrained('tickets');
            $table->foreignUuid('user_id')->constrained('users');
            $table->foreignUuid('invited_by_user_id')->constrained('users');
            $table->enum('role_in_ticket', ['client', 'staff', 'leader', 'supporter', 'admin']);
            $table->timestamp('joined_at');
            $table->timestamp('left_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_participants');
    }
};
