<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->string('related_product_type', 32)->nullable();
            $table->unsignedBigInteger('related_product_id')->nullable();
            $table->index(['related_product_type', 'related_product_id'], 'posts_related_product_index');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropIndex('posts_related_product_index');
            $table->dropColumn(['related_product_type', 'related_product_id']);
        });
    }
};
