<?php

namespace App\Console\Commands;

use App\Imports\ProductsImport;
use App\Services\ProductImportService;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ImportProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get data from excel file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $runs = app(ProductImportService::class);
        $import = $runs->start('artisan', 'products.xlsx');
        try {
            $productsImport = new ProductsImport($import->id);
            Excel::import($productsImport, public_path('excel/products.xlsx'));
            $runs->finish($import);
            $this->info('Импорт продуктов завершен.');

            // После завершения импорта, выводим собранные ошибки
            $failures = $productsImport->getFailures();
            if (!empty($failures)) {
                $this->warn('Обнаружены ошибки в следующих строках:');
                foreach ($failures as $failure) {
                    $this->error("Строка {$failure['row']}: Поле '{$failure['attribute']}' - " . implode(', ', $failure['errors']) . " (Значение: " . ($failure['values'][$failure['attribute']] ?? '') . ")");
                }
                return self::FAILURE;
            }

            $this->info('Все строки были успешно импортированы.');
            return self::SUCCESS;
        } catch (Throwable $exception) {
            $runs->finish($import, $exception);
            throw $exception;
        }
    }
}
