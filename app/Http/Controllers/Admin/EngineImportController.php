<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EngineCatalogImportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EngineImportController extends Controller
{
    public function index(): View
    {
        return view('admin.engine.import');
    }

    public function import(Request $request, EngineCatalogImportService $importer): RedirectResponse
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:xlsx,csv|max:61440',
        ]);

        try {
            $report = $importer->importFile($data['file']);
        } catch (\Throwable $error) {
            return redirect()->route('admin.engines.import.form')
                ->withErrors(['file' => 'Не удалось прочитать файл: '.$error->getMessage()]);
        }

        return redirect()->route('admin.engines.import.form')->with('engine_import_report', $report);
    }

    public function template()
    {
        $headers = [
            'sku', 'title', 'manufacturer', 'engine_family', 'model', 'type', 'price',
            'currency', 'availability', 'image', 'description',
        ];
        $examples = [
            ['SAMPLE-MAN-D2676-LB', 'MAN D2676 Long Block', 'MAN', 'D26', 'D2676', 'long_block', '', 'EUR', 'On request', '/images/product-image.jpg', 'Пример строки каталога'],
            ['SAMPLE-MAN-D2066-ENG', 'MAN D2066 Engine', 'MAN', 'D20', 'D2066', 'engine', '', 'EUR', '7-14 days', '/images/product-image.jpg', 'Пример строки каталога'],
            ['SAMPLE-VOLVO-D13-ENG', 'Volvo D13 Engine', 'Volvo', 'D13', 'D13K', 'engine', '', 'EUR', 'On request', '/images/product-image.jpg', 'Пример строки каталога'],
            ['SAMPLE-PENTA-D13-ENG', 'Volvo Penta D13 Marine Engine', 'Volvo Penta', 'D13', 'D13-800', 'engine', '', 'EUR', 'On request', '/images/product-image.jpg', 'Пример строки каталога'],
            ['SAMPLE-SCANIA-DI13-ENG', 'Scania DI13 Marine Engine', 'Scania Marine', 'DI13', 'DI13 076M', 'engine', '', 'EUR', 'In stock', '/images/product-image.jpg', 'Пример строки каталога'],
            ['SAMPLE-SCANIA-DI16-LB', 'Scania DI16 Long Block', 'Scania', 'DI16', 'DI16', 'long_block', '', 'EUR', 'Под заказ', '/images/product-image.jpg', 'Пример строки каталога'],
        ];

        return response()->streamDownload(static function () use ($headers, $examples): void {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, $headers, ',', '"', '');
            foreach ($examples as $row) {
                fputcsv($stream, $row, ',', '"', '');
            }
            fclose($stream);
        }, 'engines-import-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
