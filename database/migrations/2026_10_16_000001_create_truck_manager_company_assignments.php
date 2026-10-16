<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('truck_manager_company', function (Blueprint $table) {
            $table->foreignId('manager_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_admin_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['manager_id', 'company_admin_id']);
            $table->index('company_admin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('truck_manager_company');
    }
};
