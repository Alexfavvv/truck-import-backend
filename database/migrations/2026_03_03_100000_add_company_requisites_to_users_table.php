<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('inn')->nullable()->after('requisites');
            $table->string('ogrn')->nullable()->after('inn');
            $table->string('bank_account')->nullable()->after('ogrn');
            $table->string('bank')->nullable()->after('bank_account');
            $table->string('bik')->nullable()->after('bank');
            $table->string('correspondent_account')->nullable()->after('bik');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'inn', 'ogrn', 'bank_account', 'bank', 'bik', 'correspondent_account',
            ]);
        });
    }
};
