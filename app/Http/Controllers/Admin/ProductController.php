<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreRequest;
use App\Http\Requests\Admin\Product\UpdateRequest;
use App\Http\Requests\ImportRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\Admin\ProductService;
use App\Services\ImportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with([
            'category.parent',
            'brand'
        ])->orderBy('id')->paginate(15);

        return view('admin.product.index', compact('products'));
    }

    public function create(): View
    {
        $brands = Brand::all();
        $categories = Category::whereNotNull('parent_id')
            ->where('type', 'part')
            ->with('parent')
            ->orderBy('title')
            ->get()
            ->sortBy([fn($a, $b) => strcmp($a->parent->title, $b->parent->title)]);

        return view('admin.product.create', ['categories' => $categories, 'brands' => $brands]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $data = $request->except('product.image');
        ProductService::store($data);
        return redirect()->route('admin.products.create');
    }

    public function edit(Product $product): View
    {
        $brands = Brand::all();
        $categories = Category::whereNotNull('parent_id')
            ->where('type', 'part')
            ->with('parent')
            ->orderBy('title')
            ->get()
            ->sortBy([fn($a, $b) => strcmp($a->parent->title, $b->parent->title)]);

        return view('admin.product.edit', compact('product', 'brands', 'categories'));
    }

    public function update(Product $product, UpdateRequest $request): RedirectResponse
    {
        $data = $request->except('product.image');
        ProductService::update($data, $product);
        return redirect()->route('admin.products.index');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();
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
