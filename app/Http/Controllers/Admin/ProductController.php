<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Console\Commands\ImportReadyPricelists;
use App\Http\Requests\Admin\Product\StoreRequest;
use App\Http\Requests\Admin\Product\UpdateRequest;
use App\Http\Requests\ImportRequest;
use App\Models\Brand;
use App\Models\PartType;
use App\Models\Product;
use App\Models\ProductImport;
use App\Models\TruckManufacturer;
use App\Jobs\Product\ProcessReadyProductImport;
use App\Services\Admin\ProductService;
use App\Services\ImportService;
use App\Services\ProductImportService;
use App\Services\ProductPriceService;
use Illuminate\Database\QueryException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query();
        if ($request->has('search') && $request->search != null) {
            $search = $request->search;
            $query->where('title', 'like', "%$search%")
                ->orWhere('sku', 'like', "%$search%");
        }

        $products = $query->with([
            'brand'
        ])->orderBy('id')->paginate(15);

        return view('admin.product.index', compact('products'));
    }

    public function create(): View
    {
        $brands = Brand::orderBy('title')->get();
        $partTypes = PartType::orderBy('title')->get();
        $truckManufacturers = TruckManufacturer::orderBy('title')->get();
        return view('admin.product.create', compact('brands', 'partTypes', 'truckManufacturers'));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $data = $request->except('product.image');
        ProductService::store($data);
        session()->flash('success', 'Товар успешно добавлен!');
        return redirect()->route('admin.products.create');
    }

    public function edit(Product $product): View
    {
        // Используем eager loading, чтобы избежать N+1 запросов
        $product->load('truckManufacturers', 'metatags', 'characteristics');

        // Получаем все необходимые данные для выпадающих списков
        $brands = Brand::orderBy('title')->get();
        $partTypes = PartType::orderBy('title')->get();
        $truckManufacturers = TruckManufacturer::orderBy('title')->get();
        $automaticPrice = null;
        if ($product->supplier_price !== null && $product->exchange_rate !== null
            && $product->price_multiplier !== null && $product->markup_percent !== null) {
            $automaticPrice = app(ProductPriceService::class)->calculateSellingPrice(
                $product->supplier_price,
                $product->exchange_rate,
                $product->price_multiplier,
                $product->markup_percent,
            );
        }

        return view('admin.product.edit', compact(
            'product',
            'brands',
            'partTypes',
            'truckManufacturers',
            'automaticPrice',
        ));
    }

    public function update(Product $product, UpdateRequest $request): RedirectResponse
    {
        $data = $request->except('product.image');
        ProductService::update($data, $product);
        session()->flash('success', 'Товар успешно обновлен!');
        return redirect()->route('admin.products.index');
    }

    public function destroy(Product $product): RedirectResponse
    {
        try {
            $product->delete();
            session()->flash('success', 'Товар успешно удален!');
        } catch (QueryException $e) {
            if (($e->errorInfo[0] ?? null) === '23503') {
                session()->flash(
                    'error',
                    'Нельзя удалить товар, так как он используется в заказах. Сначала удалите/измените связанные позиции заказов.'
                );
                return redirect()->route('admin.products.index');
            }

            throw $e;
        }

        return redirect()->route('admin.products.index');
    }

    public function destroyAll(): RedirectResponse
    {
        $deleted = 0;

        Product::query()
            ->whereDoesntHave('orderItems')
            ->select(['id'])
            ->chunkById(200, function ($products) use (&$deleted) {
                foreach ($products as $product) {
                    $product->delete();
                    $deleted++;
                }
            });

        $blocked = Product::query()
            ->whereHas('orderItems')
            ->count();

        if ($deleted > 0) {
            $message = "Удалено товаров: {$deleted}.";
            if ($blocked > 0) {
                $message .= " Не удалено (используются в заказах): {$blocked}.";
            }
            session()->flash('success', $message);
        } elseif ($blocked > 0) {
            session()->flash('error', "Все товары связаны с заказами. Удаление невозможно. Кол-во: {$blocked}.");
        } else {
            session()->flash('error', 'Товары для удаления не найдены.');
        }

        return redirect()->route('admin.products.index');
    }

    public function showImportForm()
    {
        return view('admin.product.import');
    }

    public function previewReadyImport(Request $request, ImportReadyPricelists $ready): View
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:xlsx|max:61440',
            'currency' => ['required', 'regex:/^[A-Za-z]{3}$/D'],
            'exchange_rate' => 'required|numeric|gt:0',
            'price_multiplier' => 'required|numeric|gt:0',
            'markup_percent' => 'required|numeric|min:0',
        ]);
        $pricing = [
            'supplier_currency' => strtoupper($data['currency']),
            'exchange_rate' => (string) $data['exchange_rate'],
            'price_multiplier' => (string) $data['price_multiplier'],
            'markup_percent' => (string) $data['markup_percent'],
        ];

        try {
            $preview = $ready->previewFile($data['file']->getRealPath(), $pricing);
        } catch (\InvalidArgumentException|\RuntimeException $error) {
            throw ValidationException::withMessages(['file' => $error->getMessage()]);
        }

        $old = session('ready_import_preview');
        if (is_array($old) && isset($old['path'])) Storage::disk('local')->delete($old['path']);
        session()->forget('ready_import_preview');
        $filename = $data['file']->getClientOriginalName();
        if ($preview['summary']['valid_rows'] === 0) {
            return view('admin.product.import', compact('preview', 'pricing', 'filename'));
        }

        $path = Storage::disk('local')->putFileAs('imports/ready-preview', $data['file'], Str::random(40).'.xlsx');
        if ($path === false) throw new \RuntimeException('Не удалось сохранить XLSX для запуска импорта.');
        $token = Str::random(64);
        session(['ready_import_preview' => [
            'token' => $token,
            'path' => $path,
            'filename' => $data['file']->getClientOriginalName(),
            'pricing' => $pricing,
        ]]);

        return view('admin.product.import', compact('preview', 'token', 'pricing', 'filename'));
    }

    public function confirmReadyImport(Request $request, ProductImportService $runs): RedirectResponse
    {
        $data = $request->validate(['preview_token' => 'required|string']);
        $pending = session('ready_import_preview');
        if (!is_array($pending) || !hash_equals($pending['token'] ?? '', $data['preview_token'])
            || !Storage::disk('local')->exists($pending['path'] ?? '')) abort(419);

        session()->forget('ready_import_preview');
        try {
            $run = $runs->start('ready_import', $pending['filename'], auth()->id(), $pending['pricing']);
            ProcessReadyProductImport::dispatch(
                $pending['path'], $pending['filename'], $pending['pricing'], $run->id, auth()->id()
            );
        } catch (\Throwable $error) {
            $currentRun = isset($run) ? $run->fresh() : null;
            if ($currentRun && $currentRun->finished_at === null) $runs->finish($currentRun, $error);
            Storage::disk('local')->delete($pending['path']);
            throw $error;
        }
        return redirect()->route('admin.products.import.ready.result', $run);
    }

    public function readyImportResult(ProductImport $import): View
    {
        abort_unless($import->source === 'ready_import' && (string) $import->created_by === (string) auth()->id(), 404);
        return view('admin.product.import-result', compact('import'));
    }

    public function import(ImportRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $response = ProductService::import($file);
        return response()->json($response, $response['status']);
    }

    /**
     * Этот метод будет использоваться для Long Polling с фронтенда.
     * @return JsonResponse
     */
    public function getImportStatus(): JsonResponse
    {
        $userId = auth()->user()->id;
        $statusData = ImportService::getImportStatus($userId);
        return response()->json($statusData, $statusData['http_status'] ?? 200);
    }
}
