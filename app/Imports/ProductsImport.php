<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\PartType;
use App\Models\Product;
use App\Models\TruckManufacturer;
use App\Services\ProductPriceService;
use App\Services\ProductImportService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Validators\Failure;

class ProductsImport implements OnEachRow, WithHeadingRow, WithValidation, SkipsOnFailure, WithChunkReading
{
    private array $failures = [];
    private array $failedRows = [];

    public function __construct(private ?int $importId = null, private ?int $userId = null) {}

    public function onRow(Row $row)
    {
        $row      = $row->toArray();

        DB::transaction(function () use ($row) {
            // Находим или создаем новый продукт
            $product = Product::query()->firstOrNew(['sku' => $row['artikul']]);
            $product->title = $row['nazvanie_tovara'];
            $product->quantity = (int) $row['kolicestvo'];
            $product->description = $row['opisanie'] ?? null;
            $product->delivery = $row['vremia_dostavki'] ?? null;

            // Присвоение бренда
            $brandTitle = $row['brend'];
            $brand = Brand::query()->firstOrCreate(
                ['title' => $brandTitle],
                ['title' => $brandTitle, 'slug' => Str::slug($brandTitle)]
            );
            $product->brand_id = $brand->id;

            // Присвоение типа детали
            $partTypeTitle = $row['tip_detali'];
            $partType = PartType::query()->firstOrCreate(
                ['title' => $partTypeTitle],
                ['title' => $partTypeTitle, 'slug' => Str::slug($partTypeTitle)]
            );
            $product->part_type_id = $partType->id;

            // Сохраняем продукт, чтобы у него был ID для связей
            $created = !$product->exists;
            app(ProductPriceService::class)->applySalePrice(
                $product, $row['cena'], 'import', $this->userId, $this->importId
            );

            // Присвоение совместимых брендов
            $truckManufacturers = $row['sovmestimost'] ?? null;
            $truckManufacturerIds = [];

            if (!empty($truckManufacturers)) {
                $manufacturerTitles = explode(';', $truckManufacturers);
                $manufacturerTitles = array_map('trim', $manufacturerTitles);
                $manufacturerTitles = array_filter($manufacturerTitles); // Удаляем пустые строки после trim

                foreach ($manufacturerTitles as $manufacturerTitle) {
                    $manufacturer = TruckManufacturer::query()->firstOrCreate(
                        ['title' => $manufacturerTitle],
                        ['title' => $manufacturerTitle, 'slug' => Str::slug($manufacturerTitle)]
                    );
                    $truckManufacturerIds[] = $manufacturer->id;
                }
            }

            // Синхронизируем совместимых производителей.
            $product->truckManufacturers()->sync($truckManufacturerIds);

            // Присвоение характеристик
            $characteristics = $row['xarakteristiki_tovara'] ?? null;
            $product->characteristics()->delete();

            if ($characteristics) {
                $characteristics = explode(';', $characteristics);
                foreach ($characteristics as $characteristic) {
                    $characteristic = trim($characteristic);
                    if (empty($characteristic)) continue;

                    $characteristic = explode('>', $characteristic);
                    $characteristicTitle = trim($characteristic[0]);
                    $characteristicValue = isset($characteristic[1]) ? trim($characteristic[1]) : null;

                    $product->characteristics()->create([
                        'title' => $characteristicTitle,
                        'description' => $characteristicValue,
                    ]);
                }
            }
            app(ProductImportService::class)->count($this->importId, $created ? 1 : 0, $created ? 0 : 1);
        });
    }

    public function rules(): array
    {
        return [
            'nazvanie_tovara' => 'required|string|max:255',
            'artikul' => 'required|string|max:100',
            'cena' => 'required|numeric|min:0|max:9999999999999.99',
            'kolicestvo' => 'required|integer|min:0',
            'brend' => 'required|string',
            'tip_detali' => 'required|string',
            'sovmestimost' => 'nullable|string',
            'opisanie' => 'nullable|string',
            'vremia_dostavki' => 'nullable|string',
            'xarakteristiki_tovara' => 'nullable|string',
        ];
    }

    // Внутри класса ProductsImport, но вне onRow() и rules()
    public function onFailure(Failure ...$failures)
    {
        // Каждая ошибка (Failure) содержит row(), attribute(), errors(), values()
        $newFailures = 0;
        foreach ($failures as $failure) {
            if (!isset($this->failedRows[$failure->row()])) {
                $this->failedRows[$failure->row()] = true;
                $newFailures++;
            }
            $this->failures[] = [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
            ];
        }
        app(ProductImportService::class)->count($this->importId, failed: $newFailures);
    }

// Получить собранные ошибки из команды
    public function getFailures(): array
    {
        return $this->failures;
    }

    public function chunkSize(): int
    {
        return 200;
    }

}
