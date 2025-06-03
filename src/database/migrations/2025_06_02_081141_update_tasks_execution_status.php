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
        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('execution_status', [
                'not_started',
                'in_progress', 
                'blocked',
                'change_requested',
                're_estimation_pending',
                'ready_for_review',
                'done'
            ])->default('not_started')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->enum('execution_status', [
                'not_started',
                'in_progress', 
                'blocked',
                'change_requested',
                're_estimation_pending',
                'done'
            ])->default('not_started')->change();
        });
    }
};
