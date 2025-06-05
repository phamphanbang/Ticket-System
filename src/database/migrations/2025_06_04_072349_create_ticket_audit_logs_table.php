<?php

use App\Constants\TicketStatus;
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
            $table->string('action');
            $table->string('status');
            $table->string('to_status')->nullable();
            $table->foreignUuid('holder_id')->nullable()->constrained('users');
            $table->foreignUuid('staff_id')->nullable()->constrained('users');
            $table->timestamp('start_at');
            $table->timestamp('end_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
