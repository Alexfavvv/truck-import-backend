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
        if (Schema::hasColumn('users', 'login')) {
            return;
        }
        Schema::table('users', function (Blueprint $table) {
            // Логин для авторизации (может быть null, чтобы не ломать существующие записи)
            $table->string('login')->nullable()->unique()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('users', 'login')) {
            return;
        }
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('login');
        });
    }
};

