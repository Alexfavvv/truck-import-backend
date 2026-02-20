<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('login')->nullable()->unique()->after('name');
            $table->string('role')->nullable()->after('is_admin');
            $table->foreignId('parent_id')->nullable()->constrained('users')->nullOnDelete()->after('role');
            $table->boolean('is_active')->default(true)->after('parent_id');
            $table->string('customer_id')->nullable()->unique()->after('is_active');
            $table->string('phone')->nullable()->after('customer_id');
            $table->string('company')->nullable()->after('phone');
            $table->text('requisites')->nullable()->after('company');
            $table->string('full_name')->nullable()->after('requisites');
            $table->string('position')->nullable()->after('full_name');
            // email делаем необязательным (у менеджеров может не быть)
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn([
                'login', 'role', 'parent_id', 'is_active', 'customer_id',
                'phone', 'company', 'requisites', 'full_name', 'position',
            ]);
            $table->string('email')->nullable(false)->change();
        });
    }
};
