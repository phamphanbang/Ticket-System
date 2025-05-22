<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'staff'])->nullable()->after('email');
        });

        // Map role_id values to enum strings
        DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->update(['users.role' => DB::raw('roles.role')]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']); // Optional, only if foreign key exists
            $table->dropColumn('role_id');
        });

        Schema::dropIfExists('roles');

        // Make role column NOT NULL after data is moved
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'staff'])->default('staff')->change();
        });
    }

    public function down(): void
    {
        // Optional: Recreate roles table and role_id if needed
    }
};
