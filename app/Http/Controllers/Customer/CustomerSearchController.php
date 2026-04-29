<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\PartSearchListImportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerSearchController extends Controller
{
    private const PREVIOUS_SEARCHES_KEY = 'customer_part_search_history';

    private const MAX_PREVIOUS_SEARCHES = 15;

    public function index(Request $request): View
    {
        $query = $request->input('q', '');
        $query = is_string($query) ? trim($query) : '';
        $products = collect();

        if ($query !== '') {
            $products = Product::query()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', '%'.$query.'%')
                        ->orWhere('sku', 'like', '%'.$query.'%');
                })
                ->with('brand', 'partType')
                ->orderBy('id')
                ->paginate(20)
                ->withQueryString();

            $this->addToPreviousSearches($query);
        }

        $previousSearches = $this->getPreviousSearches();

        $previousProducts = collect();

        // Текущий поиск: добавляем товары с текущей страницы результатов в историю
        if ($query !== '' && $products->isNotEmpty()) {
            $currentItems = $products->getCollection();
            if ($currentItems->isNotEmpty()) {
                $previousProducts = $previousProducts->merge($currentItems);
            }
        }

        foreach ($previousSearches as $term) {
            $term = trim((string) $term);
            if ($term === '') {
                continue;
            }

            $items = Product::query()
                ->where(function ($q) use ($term) {
                    $q->where('title', 'like', '%'.$term.'%')
                        ->orWhere('sku', 'like', '%'.$term.'%');
                })
                ->with('brand', 'partType')
                ->orderByRaw('CASE WHEN sku = ? THEN 0 ELSE 1 END', [$term])
                ->orderBy('id')
                ->limit(15)
                ->get();

            $previousProducts = $previousProducts->merge($items);
        }

        $previousProducts = $previousProducts->unique('id')->values();

        return view('customer.search.index', [
            'query' => $query,
            'products' => $products,
            'previousSearches' => $previousSearches,
            'previousProducts' => $previousProducts,
        ]);
    }

    public function uploadList(Request $request, PartSearchListImportService $importService): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:xlsx,xls,csv'],
        ]);

        $parsed = $importService->parseUploadedFile($request->file('file'));
        $resolved = $importService->resolveProducts($parsed);

        $rowsHtml = [];
        foreach ($resolved['items'] as $item) {
            $rowsHtml[] = view('partials.search-product-table-row', [
                'product' => $item['product'],
                'quantity' => $item['quantity'],
                'checked' => true,
            ])->render();
        }

        return response()->json([
            'rows_html' => $rowsHtml,
            'not_found' => $resolved['not_found'],
            'matched_count' => count($resolved['items']),
        ]);
    }

    private function getPreviousSearches(): array
    {
        return session(self::PREVIOUS_SEARCHES_KEY, []);
    }

    private function addToPreviousSearches(string $term): void
    {
        $list = session(self::PREVIOUS_SEARCHES_KEY, []);
        $term = trim($term);
        if ($term === '') {
            return;
        }
        $list = array_values(array_filter($list, fn ($item) => $item !== $term));
        array_unshift($list, $term);
        $list = array_slice($list, 0, self::MAX_PREVIOUS_SEARCHES);
        session([self::PREVIOUS_SEARCHES_KEY => $list]);
    }
}
