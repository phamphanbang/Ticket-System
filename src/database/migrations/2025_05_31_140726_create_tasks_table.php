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
            $table->enum('estimation_status', ['assigned', 'ready_for_review', 'needs_revision', 'finalized']);
            $table->enum('execution_status', ['in_progress', 'blocked', 'change_requested', 're_estimation_pending', 'done']);
            $table->enum('phase', ['estimation', 'execution']);
            $table->integer('estimated_time');
            $table->integer('actual_time');
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
