<?php

namespace App\Services\Admin;

use App\Jobs\Product\ProcessProductImport;
use App\Models\Product;
use App\Services\ImportService;
use App\Services\ProductImportService;
use App\Services\ProductPriceService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService
{
    public static function store(array $data): Product
    {
        try {
            DB::beginTransaction();
            $fields = $data['product'];
            $price = $fields['price'];
            unset($fields['price']);
            $product = new Product($fields);
            app(ProductPriceService::class)->setManualPrice($product, $price, auth()->id());
            $product->metatags()->create($data['meta']);
            $product->truckManufacturers()->sync($data['truckManufacturers']);
            $product->characteristics()->createMany($data['characteristics']);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            abort(500, $e->getMessage());
        }

        return $product;
    }

    public static function update(array $data, Product $product): Product
    {
        try {
            DB::beginTransaction();
            $product = Product::query()->lockForUpdate()->findOrFail($product->id);
            $fields = $data['product'];
            $prices = app(ProductPriceService::class);
            $requestedPrice = $prices->decimal($fields['price']);
            $manualPriceProvided = array_key_exists('manual_price', $fields);
            $manualPrice = $fields['manual_price'] ?? null;
            unset($fields['manual_price']);
            unset($fields['price']);
            $changed = $requestedPrice !== $prices->decimal($product->price);
            $product->fill($fields);
            if (!empty($data['reset_manual_price'])) {
                if ($product->manual_price !== null) $prices->clearManualPrice($product, auth()->id());
                else $product->save();
            } elseif ($manualPriceProvided) {
                if ($manualPrice === null) {
                    if ($product->manual_price !== null) $prices->clearManualPrice($product, auth()->id());
                    else $product->save();
                } else {
                    $prices->setManualPrice($product, $manualPrice, auth()->id());
                }
            } elseif ($changed) {
                $prices->setManualPrice($product, $requestedPrice, auth()->id());
            } else {
                // Saving a title/description must not accidentally enable a price override.
                $product->save();
            }
            $product->metatags()->update($data['meta']);
            $product->truckManufacturers()->sync($data['truckManufacturers']);
            $product->characteristics()->delete();
            $product->characteristics()->createMany($data['characteristics']);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            abort(500, $e->getMessage());
        }

        return $product;
    }

    public static function import(UploadedFile $file): array
    {
        $import = null;
        try {
            $runs = app(ProductImportService::class);
            $import = $runs->start('admin_upload', $file->getClientOriginalName(), auth()->id());
            ImportService::handleFileAndDispatchJob($file, ProcessProductImport::class, $import->id);

            return [
                'success' => true,
                'message' => 'Файл импорта продуктов успешно загружен и отправлен в обработку.',
                'status' => 200
            ];
        } catch (\Throwable $exception) {
            if ($import) app(ProductImportService::class)->finish($import, $exception);
            Log::error('Ошибка при импорте Excel-файла: ' . $exception->getMessage(), [
                'file' => $file ? $file->getClientOriginalName() : 'N/A',
                'exception' => $exception->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Произошла ошибка при загрузке или обработке файла: ' . $exception->getMessage(),
                'status' => 500
            ];
        }
    }
}
