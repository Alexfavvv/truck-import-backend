<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_imports', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('filename');
            $table->string('status')->default('queued')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            foreach (['rows_total', 'rows_created', 'rows_updated', 'rows_failed'] as $field) {
                $table->unsignedBigInteger($field)->default(0);
            }
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
        });
        Schema::create('product_price_history', function (Blueprint $table) {
            $table->id();
            // Keep price history after a product is deleted; retain its original SKU.
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_sku');
            foreach (['supplier_price', 'automatic_price', 'selling_price', 'manual_price'] as $field) {
                $table->decimal('old_'.$field, 15, 2)->nullable();
                $table->decimal('new_'.$field, 15, 2)->nullable();
            }
            $table->string('source');
            $table->foreignId('product_import_id')->nullable()->constrained('product_imports')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at');
            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_history');
        Schema::dropIfExists('product_imports');
    }
};
