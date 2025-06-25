<?php

namespace App\Console\Commands;

use App\Imports\ProductsImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

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
        $productsImport = new ProductsImport();
        Excel::import($productsImport, public_path('excel/products.xlsx'));
        $this->info('Импорт продуктов завершен.');

        // После завершения импорта, выводим собранные ошибки
        $failures = $productsImport->getFailures();
        if (!empty($failures)) {
            $this->warn('Обнаружены ошибки в следующих строках:');
            foreach ($failures as $failure) {
                $this->error("Строка {$failure['row']}: Поле '{$failure['attribute']}' - " . implode(', ', $failure['errors']) . " (Значение: " . $failure['values'][$failure['attribute']] . ")");
            }
            return 1; // Можно вернуть 1, чтобы указать на частичный успех/ошибки
        }

        $this->info('Все строки были успешно импортированы.');
    }
}
