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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_type_id')->constrained('part_types')->onDelete('cascade');
            $table->foreignId('brand_id')->index()->constrained('brands')->onDelete('cascade');
            $table->string('sku')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->float('price');
            $table->string('delivery')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
