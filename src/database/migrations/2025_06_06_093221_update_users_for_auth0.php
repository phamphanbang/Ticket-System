<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('auth0_id')->unique()->nullable()->after('email');
            $table->string('avatar')->nullable()->after('name');
            $table->dropColumn('password');
            $table->string('password')->nullable()->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('auth0_id');
            $table->dropColumn('avatar');
            $table->string('password')->change(); // revert to NOT NULL
        });
    }
};
