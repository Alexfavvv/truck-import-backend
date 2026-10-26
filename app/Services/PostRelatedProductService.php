<?php

namespace App\Services;

use App\Models\Engine;
use App\Models\EngineBlock;
use App\Models\Gearbox;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PostRelatedProductService
{
    private const CATALOGS = [
        'engine' => [
            'model' => Engine::class,
            'catalog' => 'engines',
            'label' => 'Двигатель',
            'search_fields' => ['title', 'manufacturer', 'model', 'sku', 'code_variants'],
        ],
        'engine_block' => [
            'model' => EngineBlock::class,
            'catalog' => 'engine-blocks',
            'label' => 'Блок двигателя',
            'search_fields' => ['title', 'manufacturer', 'model', 'sku', 'oem_code', 'oem_code_compact', 'code_variants'],
        ],
        'gearbox' => [
            'model' => Gearbox::class,
            'catalog' => 'gearboxes',
            'label' => 'Коробка передач',
            'search_fields' => ['title', 'manufacturer', 'model', 'sku', 'code_variants'],
        ],
    ];

    public function find(?string $type, ?int $id): ?Model
    {
        if (!$type || !$id || !isset(self::CATALOGS[$type])) {
            return null;
        }

        $model = self::CATALOGS[$type]['model'];

        return $model::query()->find($id);
    }

    public function search(string $term): array
    {
        $term = trim($term);
        if (mb_strlen($term) < 2) {
            return [];
        }

        $results = [];
        foreach (self::CATALOGS as $type => $catalog) {
            $model = $catalog['model'];
            $products = $model::query()
                ->where(function (Builder $query) use ($catalog, $term): void {
                    foreach ($catalog['search_fields'] as $field) {
                        $column = $query->getModel()->qualifyColumn($field);
                        $query->orWhereRaw('LOWER('.$column.') LIKE ?', ['%'.mb_strtolower($term).'%']);
                    }
                })
                ->orderBy('title')
                ->limit(5)
                ->get();

            foreach ($products as $product) {
                $results[] = $this->productPayload($type, $product);
            }
        }

        return array_slice($results, 0, 15);
    }

    public function payload(?string $type, ?int $id): ?array
    {
        $product = $this->find($type, $id);
        if (!$product || !$type) {
            return null;
        }

        return $this->productPayload($type, $product);
    }

    private function productPayload(string $type, Model $product): array
    {
        $catalog = self::CATALOGS[$type];
        $price = is_numeric($product->price) && (float) $product->price > 0
            ? (float) $product->price
            : null;

        return [
            'type' => $type,
            'id' => (int) $product->getKey(),
            'title' => (string) $product->title,
            'manufacturer' => $product->manufacturer,
            'model' => $product->model,
            'sku' => $product->sku ?: ($product->oem_code ?? $product->oem_code_compact ?? null),
            'product_type' => $catalog['label'],
            'price' => $price,
            'price_text' => $product->price_text ?: null,
            'currency' => $product->currency ?: 'RUB',
            'image' => $product->image,
            'path' => $type === 'engine' && trim((string) $product->slug) !== ''
                ? '/'.$catalog['catalog'].'/'.$product->slug
                : null,
        ];
    }
}
