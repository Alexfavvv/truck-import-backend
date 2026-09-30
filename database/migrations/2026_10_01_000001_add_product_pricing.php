<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('supplier_price', 15, 2)->nullable();
            $table->decimal('automatic_price', 15, 2)->nullable();
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->decimal('manual_price', 15, 2)->nullable();
            $table->char('supplier_currency', 3)->nullable();
            $table->decimal('exchange_rate', 18, 8)->nullable();
            $table->decimal('price_multiplier', 18, 8)->nullable();
            $table->decimal('markup_percent', 18, 8)->nullable();
            $table->timestamp('price_updated_at')->nullable();
            // TODO: confirm supplier units before attaching kg/cm labels.
            foreach (['weight', 'width', 'height', 'length'] as $field) {
                $table->decimal($field, 15, 6)->nullable();
            }
            $table->text('replacement')->nullable();
        });

        // Existing price is a SALE price. Do not infer supplier cost or a manual override.
        // Fail (and roll back on PostgreSQL) rather than silently clamp invalid old data.
        if (DB::table('products')->where('price', '<', 0)->orWhere('price', '>', '9999999999999.99')->exists()) {
            throw new RuntimeException('Legacy product prices exceed the supported nonnegative decimal(15,2) range.');
        }
        DB::table('products')->update([
            'automatic_price' => DB::raw('CAST(price AS DECIMAL(15, 2))'),
            'selling_price' => DB::raw('CAST(price AS DECIMAL(15, 2))'),
        ]);
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn([
            'supplier_price', 'automatic_price', 'selling_price', 'manual_price', 'supplier_currency',
            'exchange_rate', 'price_multiplier', 'markup_percent', 'price_updated_at',
            'weight', 'width', 'height', 'length', 'replacement',
        ]));
    }
};
