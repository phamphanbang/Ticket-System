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
            $table->string('title');
            $table->text('description');
            $table->enum('status', [1,2,3,4])->default(1);
            $table->string('client_name');
            $table->string('client_email');
            $table->enum('priority', [1,2,3])->default(2);
            $table->dateTime('deadline');
            $table->string('message_id')->nullable();
            $table->foreignUuid('assign_to')->nullable()->constrained('users','id')->nullOnDelete();
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
