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
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('clients');
            $table->string('subject');
            $table->text('description');
            $table->enum('internal_status', ['new','in_analysis','awaiting_estimation_approval','awaiting_client_approval','in_progress','under_review','completed','closed']);
            $table->enum('external_status', ['received','processing','awaiting_your_approval','completed','closed']);
            $table->timestamps();
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
