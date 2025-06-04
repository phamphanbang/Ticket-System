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
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('clients');
            $table->enum('status', TicketStatus::all())->default(TicketStatus::NEW->value);
            $table->string('title');
            $table->text('description');
            $table->foreignUuid('mail_created_id')->nullable()->constrained('received_mails');
            $table->foreignUuid('created_by')->constrained('users');
            $table->foreignUuid('current_processor_id')->nullable()->constrained('users');
            $table->foreignUuid('responsible_user_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->timestamp('closed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
