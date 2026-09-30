<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImport;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductImportService
{
    public function start(string $source, string $filename, ?int $userId = null, array $metadata = [], ?int $id = null): ProductImport
    {
        $run = $id ? ProductImport::findOrFail($id) : new ProductImport([
            'source' => $source, 'filename' => basename($filename), 'created_by' => $userId, 'metadata' => $metadata,
        ]);
        $run->fill(['status' => 'processing', 'started_at' => now()])->save();
        return $run;
    }

    public function finish(ProductImport $run, ?Throwable $error = null): void
    {
        $run->refresh();
        $metadata = $run->metadata ?? [];
        if ($error) {
            $message = class_basename($error).': '.mb_substr($error->getMessage(), 0, 500);
            $message = preg_replace('/(password|token|secret|authorization)(\s*[=:]\s*)\S+/i', '$1$2[redacted]', $message);
            $message = preg_replace('/(https?:\/\/)[^\s:@]+:[^\s@]+@/i', '$1[redacted]@', $message);
            $metadata['error'] = $message;
        }
        $run->update([
            'status' => $error ? 'failed' : ($run->rows_failed ? 'warning' : 'success'),
            'finished_at' => now(), 'metadata' => $metadata,
        ]);
    }

    public function count(?int $id, int $created = 0, int $updated = 0, int $failed = 0): void
    {
        if ($id === null) return;
        DB::table('product_imports')->where('id', $id)->update([
            'rows_total' => DB::raw('rows_total + '.($created + $updated + $failed)),
            'rows_created' => DB::raw('rows_created + '.$created),
            'rows_updated' => DB::raw('rows_updated + '.$updated),
            'rows_failed' => DB::raw('rows_failed + '.$failed),
        ]);
    }

    /** Atomic batch: locked current prices, common calculation, products, history and counters. */
    public function writeBatch(array $rows, string $source, array $parameters, ?int $importId): void
    {
        if (!$rows) return;
        try {
            DB::transaction(function () use ($rows, $source, $parameters, $importId) {
                $prices = app(ProductPriceService::class);
                $skus = array_column($rows, 'sku');
                if (count(array_unique($skus)) !== count($skus)) {
                    throw new \InvalidArgumentException('Duplicate SKU within import batch.');
                }
                $existing = Product::whereIn('sku', $skus)->orderBy('sku')->lockForUpdate()->get()->keyBy('sku');
                $payload = [];
                $history = [];
                $created = 0;
                foreach ($rows as $row) {
                    $product = $existing->get($row['sku']) ?? new Product();
                    $old = $prices->snapshot($product);
                    $created += $product->exists ? 0 : 1;
                    if (array_key_exists('supplier_price', $row)) {
                        $supplierPrice = $row['supplier_price'];
                        unset($row['supplier_price']);
                        $row += $prices->supplierAttributes($product, $supplierPrice, $parameters);
                    } else {
                        $salePrice = $row['sale_price'];
                        unset($row['sale_price']);
                        $row += $prices->saleAttributes($product, $salePrice);
                    }
                    $product->fill($row);
                    $change = $prices->history($old, $product, $source, null, $importId);
                    $row['price_updated_at'] = $change ? now() : $product->price_updated_at;
                    $row['updated_at'] = now();
                    $row['created_at'] = now();
                    $payload[] = $row;
                    if ($change) $history[$row['sku']] = $change;
                }
                $update = array_diff(array_keys($payload[0]), ['sku', 'created_at']);
                Product::query()->upsert($payload, ['sku'], array_values($update));
                $ids = Product::whereIn('sku', array_keys($history))->pluck('id', 'sku');
                $logs = [];
                foreach ($history as $sku => $row) $logs[] = ['product_id' => $ids[$sku]] + $row;
                if ($logs) DB::table('product_price_history')->insert($logs);
                $this->count($importId, $created, count($rows) - $created);
            });
        } catch (Throwable $error) {
            $this->count($importId, failed: count($rows));
            throw $error;
        }
    }
}
