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
        Schema::create('ticket_logs', function (Blueprint $table) {
            $table->uuid();
            $table->foreignUlid('ticket_id')->constrained('tickets','id')->cascadeOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained('users','id')->cascadeOnDelete();
            $table->enum('from_status', ['1', '2','3','4'])->default('1');
            $table->enum('to_status', ['1', '2','3','4'])->default('1');
            $table->string('action_type');
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_logs');
    }
};
