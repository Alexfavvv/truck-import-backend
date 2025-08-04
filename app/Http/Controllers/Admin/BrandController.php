<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Brand\StoreRequest;
use App\Http\Requests\Admin\Brand\UpdateRequest;
use App\Models\Brand;
use App\Models\PartType;
use App\Services\Admin\BrandService;
use App\Services\Admin\CategoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::query();

        if ($request->has('search') && $request->search != null) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
        }

        $brands = $query->paginate(10);

        return view('admin.brand.index', compact('brands'));
    }

    public function create(): View
    {
        $partTypes = PartType::orderBy('title')->get();
        return view('admin.brand.create', compact('partTypes'));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $data = $request->except('brand.image');
        BrandService::store($data);
        session()->flash('success', 'Бренд успешно добавлен!');
        return redirect()->route('admin.brands.create');
    }

    public function edit(Brand $brand): View
    {
        $brand->load('partTypes');
        $partTypes = PartType::orderBy('title')->get();
        return view('admin.brand.edit', compact('brand', 'partTypes'));
    }

    public function update(Brand $brand, UpdateRequest $request): RedirectResponse
    {
        $data = $request->except('brand.image');
        BrandService::update($data, $brand);
        session()->flash('success', 'Бренд успешно обновлен!');
        return redirect()->route('admin.brands.index');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        // TODO Сделать удаление изображения
        $brand->metatags()->delete();
        $brand->delete();
        session()->flash('success', 'Бренд успешно удален!');
        return redirect()->route('admin.brands.index');
    }
}
