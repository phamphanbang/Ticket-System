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
        Schema::create('task_audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('task_id')->constrained('tasks');
            $table->foreignUuid('changed_by')->constrained('users');
            $table->enum('current_status', ['assigned', 'ready_for_review', 'needs_revision', 'finalized', 'in_progress', 'blocked', 'change_requested', 're_estimation_pending', 'done']);
            $table->enum('current_phase', ['estimation', 'execution']);
            $table->string('field_changed');
            $table->text('old_value');
            $table->text('new_value');
            $table->text('reason')->nullable();
            $table->enum('change_type', ['update', 'correction', 'scope_change']);
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_audit_logs');
    }
};
