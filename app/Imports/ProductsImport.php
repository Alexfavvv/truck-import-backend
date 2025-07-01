<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Validators\Failure;

class ProductsImport implements OnEachRow, WithHeadingRow, WithValidation, SkipsOnFailure
{
    private array $failures = [];

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row      = $row->toArray();

//      Находим или создаем новый продукт
        $product = Product::query()->firstOrNew(['sku' => $row['artikul']]);
        $product->title = $row['nazvanie_tovara'];
        $product->price = (float) $row['cena'];
        $product->quantity = (int) $row['kolicestvo'];
        $product->description = $row['opisanie'] ?? null;
        $product->delivery = $row['vremia_dostavki'] ?? null;

        // Присвоение brendа
        $brandTitle = $row['brend'];
        $brand = Brand::query()->firstOrCreate(['title' => $brandTitle], ['title' => $brandTitle]);
        $product->brand_id = $brand->id;

        // Присвоение категории
        $categories = explode('>', $row['kategoriia']);
        $categories = array_map('trim', $categories);
        if (count($categories) !== 2) {
            $this->failures[] = new Failure(
                $rowIndex,
                'kategoriia',
                ['Неверный формат категории. Ожидается "Главная > Подкатегория".'],
                $row
            );

            return;
        }

        $manufacturer = Category::query()->firstOrCreate(
            ['title' => $categories[0], 'parent_id' => null],
            ['title' => $categories[0], 'slug' => Str::slug($categories[0]), 'type' => 'manufacturer']
        );
        $part = Category::query()->firstOrCreate(
            ['title' => $categories[1], 'parent_id' => $manufacturer->id],
            ['title' => $categories[1], 'slug' => Str::slug($categories[1]), 'type' => 'part']
        );
        $product->category_id = $part->id;

        $product->save();


        // Присвоение характеристик
        $characteristics = $row['xarakteristiki_tovara'] ?? null;

        if ($characteristics) {
            $product->characteristics()->delete();
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
    }

    public function rules(): array
    {
        return [
            'nazvanie_tovara' => 'required|string|max:255',
            'artikul' => 'required|string|max:100',
            'cena' => 'required|numeric|min:0',
            'kolicestvo' => 'required|integer|min:0',
            'brend' => 'required|string',
            'kategoriia' => 'required|string',
            'opisanie' => 'nullable|string',
            'vremia_dostavki' => 'nullable|string',
            'xarakteristiki_tovara' => 'nullable|string',
        ];
    }

    // Внутри класса ProductsImport, но вне onRow() и rules()
    public function onFailure(Failure ...$failures)
    {
        // Каждая ошибка (Failure) содержит row(), attribute(), errors(), values()
        foreach ($failures as $failure) {
            $this->failures[] = [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
            ];
        }
    }

// Получить собранные ошибки из команды
    public function getFailures(): array
    {
        return $this->failures;
    }

}
