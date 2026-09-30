<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreRequest;
use App\Http\Requests\Admin\Product\UpdateRequest;
use App\Http\Requests\ImportRequest;
use App\Models\Brand;
use App\Models\PartType;
use App\Models\Product;
use App\Models\TruckManufacturer;
use App\Services\Admin\ProductService;
use App\Services\ImportService;
use App\Services\ProductPriceService;
use Illuminate\Database\QueryException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
