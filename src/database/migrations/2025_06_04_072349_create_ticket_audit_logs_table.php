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
            $table->enum('from_status', TicketStatus::all())->default(TicketStatus::NEW->value);
            $table->enum('to_status', TicketStatus::all())->default(TicketStatus::NEW->value);
            $table->foreignUuid('from_user_id')->nullable()->constrained('users');
            $table->foreignUuid('to_user_id')->nullable()->constrained('users');
            $table->timestamp('start_at');
            $table->timestamp('end_at')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
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
