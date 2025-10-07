<?php

// database/migrations/2025_09_19_000001_add_phone_to_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users','phone')) {
                $table->string('phone',25)->nullable()->after('email');
            }
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users','phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};


