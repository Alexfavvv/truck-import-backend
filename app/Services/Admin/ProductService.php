<?php

namespace App\Services\Admin;

use App\Jobs\Product\ProcessProductImport;
use App\Models\Product;
use App\Services\ImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService
{
    public static function store(array $data): Product
    {
        try {
            DB::beginTransaction();
            $product = Product::create($data['product']);
            $product->metatags()->create($data['meta']);

            if (isset($data['characteristics'])) {
                foreach ($data['characteristics'] as $characteristic) {
                    $product->characteristics()->create($characteristic);
                }
            }

            session()->put('success', 'Товар успешно добавлен!');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            abort(500, $e->getMessage());
        }

        return $product;
    }

    public static function update(array $data, Product $product): Product
    {
        try {
            DB::beginTransaction();
            $product->update($data['product']);
            $product->metatags()->updateOrCreate($data['meta']);

            if (isset($data['characteristics'])) {
                foreach ($data['characteristics'] as $characteristic) {
                    $product->characteristics()->updateOrCreate($characteristic);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            abort(500, $e->getMessage());
        }

        return $product;
    }

    public static function import(UploadedFile $file): array
    {
        try {
            ImportService::handleFileAndDispatchJob($file, ProcessProductImport::class);

            return [
                'success' => true,
                'message' => 'Файл импорта продуктов успешно загружен и отправлен в обработку.',
                'status' => 200
            ];
        } catch (\Exception $exception) {
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
