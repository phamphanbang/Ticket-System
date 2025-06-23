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
        Schema::create('imap_sync_states', function (Blueprint $table) {
            $table->id();
            $table->string('folder'); // e.g., INBOX, Sent
            $table->unsignedBigInteger('last_uid')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imap_sync_states');
    }
};
