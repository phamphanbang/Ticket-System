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
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ticket_id')->constrained('tickets');
            $table->string('title');
            $table->text('description');
            $table->foreignUuid('assigned_to')->nullable()->constrained('users');
            $table->enum('estimation_status', ['not_started', 'assigned', 'ready_for_review', 'needs_revision', 'finalized'])->default('not_started');
            $table->enum('execution_status', ['not_started', 'in_progress', 'blocked', 'change_requested', 'ready_for_review', 'completed'])->default('not_started');
            $table->enum('phase', ['estimation', 'execution'])->default('estimation');
            $table->integer('estimated_time')->nullable();
            $table->integer('actual_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
