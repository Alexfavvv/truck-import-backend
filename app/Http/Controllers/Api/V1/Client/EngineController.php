<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Engine\EngineResource;
use App\Models\Engine;
use Illuminate\Http\Request;

class EngineController extends Controller
{
    public function index(Request $request): array
    {
        $data = $request->validate([
            'manufacturer' => 'sometimes|string|max:255',
            'search' => 'sometimes|string|max:100',
            'page' => 'sometimes|integer|min:1',
            'limit' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = Engine::query();
        if (!empty($data['manufacturer'])) {
            $manufacturers = array_filter(array_map('trim', explode(',', $data['manufacturer'])));
            $query->whereIn('manufacturer', $manufacturers);
        }
        if (!empty($data['search'])) {
            $term = '%'.mb_strtolower(trim($data['search'])).'%';
            $query->where(function ($query) use ($term) {
                $query->whereRaw('LOWER(title) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(manufacturer) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(engine_family) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(model) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(sku) LIKE ?', [$term]);
            });
        }

        $page = $query->orderBy('id')->paginate(
            (int) ($data['limit'] ?? 12), ['*'], 'page', (int) ($data['page'] ?? 1)
        );

        return [
            'data' => EngineResource::collection($page->items())->resolve(),
            'meta' => [
                'total' => $page->total(),
                'per_page' => $page->perPage(),
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
            ],
        ];
    }

    public function show(Engine $engine): array
    {
        return EngineResource::make($engine)->resolve();
    }
}
