<?php

namespace Tests\Feature;

use App\Models\Engine;
use App\Models\Product;
use App\Services\EngineCatalogImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngineCatalogImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sku_upsert_updates_existing_engine_and_keeps_its_slug(): void
    {
        $importer = app(EngineCatalogImportService::class);
        $rows = [
            ['number' => 2, 'values' => $this->row('D2676-1', 'MAN D2676', 'engine', '100')],
            ['number' => 3, 'values' => $this->row('D2676-1', 'MAN D2676 Updated', 'long_block', '125.50')],
            ['number' => 4, 'values' => $this->row('D2676-2', 'MAN D2676 Updated', 'short_block', '')],
        ];

        $report = $importer->importRows($rows);

        $this->assertSame(3, $report['total']);
        $this->assertSame(2, $report['created']);
        $this->assertSame(1, $report['updated']);
        $this->assertSame(0, $report['skipped']);
        $this->assertSame(2, Engine::count());
        $this->assertSame('man-d2676', Engine::where('sku', 'D2676-1')->value('slug'));
        $this->assertSame('125.50', Engine::where('sku', 'D2676-1')->firstOrFail()->price);
        $this->assertNotSame(
            Engine::where('sku', 'D2676-1')->value('slug'),
            Engine::where('sku', 'D2676-2')->value('slug'),
        );
        $this->assertSame(0, Product::count());
    }

    public function test_invalid_rows_are_reported_and_do_not_cancel_valid_rows(): void
    {
        $report = app(EngineCatalogImportService::class)->importRows([
            ['number' => 2, 'values' => $this->row('GOOD-1')],
            ['number' => 3, 'values' => $this->row('BAD-MAN', 'Bad manufacturer', 'engine', '10', 'Other')],
            ['number' => 4, 'values' => $this->row('BAD-TYPE', 'Bad type', 'complete_engine')],
            ['number' => 5, 'values' => $this->row('BAD-PRICE', 'Bad price', 'engine', 'nope')],
        ]);

        $this->assertSame(4, $report['total']);
        $this->assertSame(1, $report['created']);
        $this->assertSame(3, $report['skipped']);
        $this->assertSame([3, 4, 5], array_column($report['errors'], 'row'));
        $this->assertSame(['BAD-MAN', 'BAD-TYPE', 'BAD-PRICE'], array_column($report['errors'], 'sku'));
        $this->assertSame(1, Engine::count());
        $this->assertSame(0, Product::count());
    }

    private function row(
        string $sku,
        string $title = 'MAN D2066 Engine',
        string $type = 'engine',
        string $price = '100',
        string $manufacturer = 'MAN',
    ): array {
        return [
            'sku' => $sku,
            'title' => $title,
            'manufacturer' => $manufacturer,
            'model' => 'D2066',
            'type' => $type,
            'price' => $price,
            'currency' => 'EUR',
        ];
    }
}
