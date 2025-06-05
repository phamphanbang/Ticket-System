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
            $table->string('status');
            $table->string('title');
            $table->text('description');
            $table->foreignUuid('mail_created_id')->nullable()->constrained('received_emails');
            $table->foreignUuid('holder_id')->nullable()->constrained('users');
            $table->foreignUuid('staff_id')->nullable()->constrained('users');
            $table->timestamps();
            $table->timestamp('closed_at')->nullable();
            $table->softDeletes();
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
