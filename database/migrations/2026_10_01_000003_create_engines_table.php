<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engines', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('manufacturer')->index();
            $table->string('model')->nullable();
            $table->string('type', 30);
            $table->string('sku')->unique();
            $table->decimal('price', 15, 2)->nullable();
            $table->string('currency', 3)->default('RUB');
            $table->string('availability')->nullable();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->string('engine_family')->nullable();
            $table->string('displacement')->nullable();
            $table->string('power')->nullable();
            $table->string('fuel_type')->nullable();
            $table->string('application')->nullable();
            $table->string('condition')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engines');
    }
};
