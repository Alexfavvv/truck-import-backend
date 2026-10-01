<?php

namespace Tests\Feature;

use App\Console\Commands\ImportReadyPricelists;
use App\Models\Product;
use App\Models\ProductImport;
use App\Models\User;
use App\Services\ProductImportService;
use App\Services\ProductPriceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ReadyPricelistAdminFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function pricing(string $markup = '2.5'): array
    {
        return [
            'supplier_currency' => 'EUR',
            'exchange_rate' => '95',
            'price_multiplier' => '2',
            'markup_percent' => $markup,
        ];
    }

    private function workbook(array $rows): string
    {
        $sheet = new Spreadsheet();
        $sheet->getActiveSheet()->fromArray([
            ['Mpn formatted', 'Weight', 'Price MSC', 'Deposit value', 'Name', 'Replacement', 'Width', 'Height', 'Length', 'Minimal amount'],
            ...$rows,
        ], null, 'A1');
        $path = sys_get_temp_dir().'/ready-admin-'.uniqid('', true).'.xlsx';
        (new Xlsx($sheet))->save($path);
        $sheet->disconnectWorksheets();
        return $path;
    }

    public function test_preview_is_read_only_and_prices_match_the_import_calculation(): void
    {
        $existing = Product::factory()->withoutImage()->create(['sku' => 'EXIST-1', 'price' => 20]);
        app(ProductPriceService::class)->setManualPrice($existing, '30000');
        $path = $this->workbook([
            ['EXIST-1', '1.25', '100', '500', 'Existing', 'ALT', '10', null, '30', '99'],
            ['NEW-1', null, '100', '500', 'New', null, null, null, null, '99'],
            ['BAD-PRICE', null, 'invalid', '500', 'Bad', null, null, null, null, '99'],
        ]);

        try {
            $beforeProducts = Product::count();
            $beforeHistory = DB::table('product_price_history')->count();
            $preview = app(ImportReadyPricelists::class)->previewFile($path, $this->pricing());
            $withoutMarkup = app(ImportReadyPricelists::class)->previewFile($path, $this->pricing('0'));

            $this->assertSame($beforeProducts, Product::count());
            $this->assertSame($beforeHistory, DB::table('product_price_history')->count());
            $this->assertSame(0, ProductImport::count());
            $this->assertSame(3, $preview['summary']['rows_scanned']);
            $this->assertSame(2, $preview['summary']['valid_rows']);
            $this->assertSame(1, $preview['summary']['invalid_rows']);
            $this->assertSame(1, $preview['summary']['existing_sku']);
            $this->assertSame(1, $preview['summary']['new_sku']);
            $this->assertSame('100.00', $preview['rows'][0]['supplier_price']);
            $this->assertSame('19475.00', $preview['rows'][0]['automatic_price']);
            $this->assertSame('30000.00', $preview['rows'][0]['final_price']);
            $this->assertSame('19475.00', $preview['rows'][1]['final_price']);
            $this->assertSame('19000.00', $withoutMarkup['rows'][0]['automatic_price']);
            $this->assertSame('invalid Price MSC', $preview['errors'][0]['reason']);
        } finally {
            @unlink($path);
        }
    }

    public function test_real_import_upserts_without_changing_manual_price_or_missing_skus(): void
    {
        $existing = Product::factory()->withoutImage()->create([
            'sku' => 'EXIST-1', 'price' => 20, 'quantity' => 11, 'description' => 'Keep description',
        ]);
        $untouched = Product::factory()->withoutImage()->create(['sku' => 'UNT-1']);
        app(ProductPriceService::class)->setManualPrice($existing, '30000');
        $path = $this->workbook([
            ['EXIST-1', '1.25', '100', '500', 'Existing', 'ALT', '10', null, '30', '99'],
            ['NEW-1', null, '100', '500', 'New', null, null, null, null, '99'],
            ['BAD-PRICE', null, 'invalid', '500', 'Bad', null, null, null, null, '99'],
        ]);

        try {
            $runs = app(ProductImportService::class);
            $run = $runs->start('ready_import', 'Pricelist Example 10.2026.xlsx');
            app(ImportReadyPricelists::class)->importFile(
                $path, 'Pricelist Example 10.2026.xlsx', $this->pricing(), $run
            );
            $runs->finish($run);

            $existing->refresh();
            $new = Product::where('sku', 'NEW-1')->firstOrFail();
            $this->assertSame('100.00', $existing->supplier_price);
            $this->assertSame('19475.00', $existing->automatic_price);
            $this->assertSame('30000.00', $existing->manual_price);
            $this->assertSame('30000.00', $existing->selling_price);
            $this->assertSame(11, $existing->quantity);
            $this->assertSame('Keep description', $existing->description);
            $this->assertSame('ALT', $existing->replacement);
            $this->assertSame('1.250000', $existing->weight);
            $this->assertSame('10.000000', $existing->width);
            $this->assertNull($existing->height);
            $this->assertSame('30.000000', $existing->length);
            $this->assertSame('100.00', $new->supplier_price);
            $this->assertSame('19475.00', $new->selling_price);
            $this->assertNotNull($new->brand_id);
            $this->assertNotNull($new->part_type_id);
            $this->assertNotNull($untouched->fresh());
            $this->assertDatabaseMissing('products', ['sku' => 'BAD-PRICE']);
            $this->assertSame('warning', $run->fresh()->status);
            $this->assertSame(1, $run->fresh()->rows_created);
            $this->assertSame(1, $run->fresh()->rows_updated);
            $this->assertSame(1, $run->fresh()->rows_failed);
            $this->assertSame('invalid Price MSC', $run->fresh()->metadata['errors'][0]['reason']);
        } finally {
            @unlink($path);
        }
    }

    public function test_admin_preview_and_confirmation_use_the_saved_file_and_parameters(): void
    {
        Storage::fake('local');
        config(['queue.default' => 'sync']);
        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $path = $this->workbook([
            ['ADMIN-1', null, '100', '500', 'Admin import', null, null, null, null, '99'],
        ]);

        try {
            $preview = $this->actingAs($admin)->post(route('admin.products.import.ready.preview'), [
                'file' => new UploadedFile($path, 'Pricelist Example 10.2026.xlsx',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true),
                'currency' => 'EUR',
                'exchange_rate' => '95',
                'price_multiplier' => '2',
                'markup_percent' => '2.5',
            ]);
            $preview->assertOk()->assertSee('19475.00');
            $this->assertDatabaseMissing('products', ['sku' => 'ADMIN-1']);
            $this->assertSame(0, ProductImport::count());

            $pending = session('ready_import_preview');
            $this->assertNotNull($pending);
            Storage::disk('local')->assertExists($pending['path']);
            $confirm = $this->post(route('admin.products.import.ready.confirm'), [
                'preview_token' => $pending['token'],
            ]);

            $run = ProductImport::firstOrFail();
            $confirm->assertRedirect(route('admin.products.import.ready.result', $run));
            $this->assertSame('success', $run->status);
            $this->assertSame($admin->id, $run->created_by);
            $this->assertSame('19475.00', Product::where('sku', 'ADMIN-1')->firstOrFail()->selling_price);
            Storage::disk('local')->assertMissing($pending['path']);
            $this->get(route('admin.products.import.ready.result', $run))
                ->assertOk()->assertSee('Pricelist Example 10.2026.xlsx');
        } finally {
            @unlink($path);
        }
    }
}
