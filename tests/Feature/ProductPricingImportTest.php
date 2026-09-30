<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\ProductImportService;
use App\Services\ProductPriceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ProductPricingImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_price_is_calculated_and_manual_price_can_be_cleared(): void
    {
        $prices = app(ProductPriceService::class);
        $product = Product::factory()->withoutImage()->create(['price' => 10]);

        $prices->applySupplierPrice($product, '100', [
            'supplier_currency' => 'EUR',
            'exchange_rate' => '95',
            'price_multiplier' => '2',
            'markup_percent' => '2.5',
        ], 'ready_import');

        $this->assertSame('19475.00', $product->fresh()->automatic_price);
        $this->assertSame('19475.00', $product->fresh()->selling_price);
        $this->assertSame('19475.00', $product->fresh()->price);

        $prices->setManualPrice($product, '120');
        $this->assertSame('120.00', $product->fresh()->selling_price);

        $prices->clearManualPrice($product);
        $this->assertNull($product->fresh()->manual_price);
        $this->assertSame('19475.00', $product->fresh()->selling_price);
    }

    public function test_legacy_sale_price_batch_does_not_become_supplier_price_or_clear_manual_price(): void
    {
        $prices = app(ProductPriceService::class);
        $imports = app(ProductImportService::class);
        $excelProduct = Product::factory()->withoutImage()->create(['price' => 10]);
        $prices->applySalePrice($excelProduct, '250', 'import');
        $this->assertNull($excelProduct->fresh()->supplier_price);
        $this->assertSame('250.00', $excelProduct->fresh()->selling_price);

        $product = Product::factory()->withoutImage()->create(['price' => 50]);
        $prices->setManualPrice($product, '80');

        $imports->writeBatch([[
            'sku' => $product->sku,
            'title' => $product->title,
            'brand_id' => $product->brand_id,
            'part_type_id' => $product->part_type_id,
            'sale_price' => '250',
            'quantity' => 7,
            'updated_at' => now(),
            'created_at' => now(),
        ]], 'fast_import', [], null);

        $product->refresh();
        $this->assertNull($product->supplier_price);
        $this->assertSame('250.00', $product->automatic_price);
        $this->assertSame('80.00', $product->manual_price);
        $this->assertSame('80.00', $product->selling_price);
        $this->assertSame('80.00', $product->price);
        $this->assertSame(7, $product->quantity);
    }

    public function test_ready_price_batch_keeps_supplier_data_and_specifications_without_touching_quantity_or_description(): void
    {
        $prices = app(ProductPriceService::class);
        $imports = app(ProductImportService::class);
        $product = Product::factory()->withoutImage()->create([
            'price' => 20,
            'quantity' => 11,
            'description' => 'Keep this description',
        ]);
        $prices->setManualPrice($product, '21000');

        $row = [
            'sku' => $product->sku,
            'title' => $product->title,
            'brand_id' => $product->brand_id,
            'part_type_id' => $product->part_type_id,
            'replacement' => 'ALT-7',
            'weight' => '1.250000',
            'width' => '10.000000',
            'height' => null,
            'length' => '30.000000',
            'supplier_price' => '100',
            'updated_at' => now(),
            'created_at' => now(),
        ];
        $parameters = [
            'supplier_currency' => 'EUR',
            'exchange_rate' => '95',
            'price_multiplier' => '2',
            'markup_percent' => '2.5',
        ];

        $imports->writeBatch([$row], 'ready_import', $parameters, null);
        $firstHistoryCount = \DB::table('product_price_history')->count();
        $imports->writeBatch([$row], 'ready_import', $parameters, null);

        $product->refresh();
        $this->assertSame('100.00', $product->supplier_price);
        $this->assertSame('19475.00', $product->automatic_price);
        $this->assertSame('21000.00', $product->selling_price);
        $this->assertSame('21000.00', $product->price);
        $this->assertSame('21000.00', $product->manual_price);
        $this->assertSame('ALT-7', $product->replacement);
        $this->assertSame('Keep this description', $product->description);
        $this->assertSame('1.250000', $product->weight);
        $this->assertSame('10.000000', $product->width);
        $this->assertNull($product->height);
        $this->assertSame('30.000000', $product->length);
        $this->assertSame(11, $product->quantity);
        $this->assertSame($firstHistoryCount, \DB::table('product_price_history')->count());
        $this->assertDatabaseHas('product_price_history', [
            'product_id' => $product->id,
            'source' => 'ready_import',
            'old_supplier_price' => null,
            'new_supplier_price' => '100.00',
            'old_selling_price' => '21000.00',
            'new_selling_price' => '21000.00',
        ]);
    }

    public function test_ready_xlsx_import_skips_empty_sku_and_bad_price_and_maps_supplier_columns(): void
    {
        $dirName = 'storage/framework/testing/ready-pricing-'.uniqid('', true);
        $dir = base_path($dirName);
        mkdir($dir, 0777, true);
        $file = $dir.'/Pricelist Example 10.2026.xlsx';

        $sheet = new Spreadsheet();
        $sheet->getActiveSheet()->fromArray([
            ['Mpn formatted', 'Weight', 'Price MSC', 'Deposit value', 'Name', 'Replacement', 'Width', 'Height', 'Length', 'Minimal amount'],
            ['', '9', '100', '500', 'Missing SKU', 'ALT-X', '9', '9', '9', '99'],
            ['BAD-PRICE', '', 'not-a-number', '500', 'Bad price', 'ALT-Y', '', '', '', '99'],
            ['SKU-KEEP', '1.25', '52.32', '500', 'Ready title', 'ALT-7', '10', null, '30', '99'],
        ], null, 'A1');
        (new Xlsx($sheet))->save($file);
        $sheet->disconnectWorksheets();

        try {
            $product = Product::factory()->withoutImage()->create([
                'sku' => 'SKU-KEEP',
                'quantity' => 12,
                'description' => 'Keep this description',
            ]);
            app(ProductPriceService::class)->setManualPrice($product, '21000');

            $this->artisan('products:import-ready', [
                '--dir' => $dirName,
                '--currency' => 'EUR',
                '--cbr' => '95',
                '--multiplier' => '2',
                '--markup' => '2.5',
            ])->assertExitCode(1);

            $product->refresh();
            $this->assertDatabaseMissing('products', ['sku' => '']);
            $this->assertDatabaseMissing('products', ['sku' => 'BAD-PRICE']);
            $this->assertSame('52.32', $product->supplier_price);
            $this->assertSame('10189.32', $product->automatic_price);
            $this->assertSame('21000.00', $product->selling_price);
            $this->assertSame('21000.00', $product->manual_price);
            $this->assertSame('Ready title', $product->title);
            $this->assertSame('ALT-7', $product->replacement);
            $this->assertSame('Keep this description', $product->description);
            $this->assertSame(12, $product->quantity);
            $this->assertSame('1.250000', $product->weight);
            $this->assertSame('10.000000', $product->width);
            $this->assertNull($product->height);
            $this->assertSame('30.000000', $product->length);
            $this->assertDatabaseHas('product_imports', [
                'source' => 'ready_import',
                'rows_failed' => 2,
                'status' => 'warning',
            ]);
        } finally {
            @unlink($file);
            @rmdir($dir);
        }
    }

    public function test_invalid_supplier_price_is_rejected_instead_of_becoming_zero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        app(ProductPriceService::class)->decimal('not-a-price');
    }
}
