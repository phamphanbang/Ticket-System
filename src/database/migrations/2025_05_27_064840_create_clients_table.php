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
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn(['client_name','client_email']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id']);
            $table->uuid('user_id');
            $table->string('user_type');
            $table->index(['user_id', 'user_type']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['client_name','client_email']);
            $table->foreignUuid('client_id')->nullable()->constrained('clients', 'id')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
