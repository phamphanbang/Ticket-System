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
        Schema::create('ticket_audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ticket_id')->constrained('tickets');
            $table->foreignUuid('changed_by')->constrained('users');
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
        Schema::dropIfExists('ticket_audit_logs');
    }
};
