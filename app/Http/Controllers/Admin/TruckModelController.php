<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TruckModel\StoreRequest;
use App\Http\Requests\Admin\TruckModel\UpdateRequest;
use App\Models\Brand;
use App\Models\TruckModel;
use App\Services\Admin\TruckModelService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TruckModelController extends Controller
{
    public function index(Request $request)
    {
        $query = TruckModel::with('brand');

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where('title', 'like', $search)
                ->orWhere('slug', 'like', $search);
        }

        $truckModels = $query->paginate(10);

        return view('admin.truckModel.index', compact('truckModels'));
    }

    public function create(): View
    {
        $truckModels = TruckModel::all();
        $brands = Brand::all();
        return view('admin.truckModel.create', compact('truckModels', 'brands'));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $data = $request->except('truckModel.image');
        TruckModelService::store($data);
        session()->flash('success', 'Модель успешно добавлена!');
        return redirect()->route('admin.truck-models.create');
    }

    public function edit(TruckModel $truckModel): View
    {
        $brands = Brand::all();
        return view('admin.truckModel.edit', compact('brands', 'truckModel'));
    }

    public function update(TruckModel $truckModel, UpdateRequest $request): RedirectResponse
    {
        $data = $request->except('truckModel.image');
        TruckModelService::update($data, $truckModel);
        session()->flash('success', 'Модель успешно обновлена!');
        return redirect()->route('admin.truck-models.index');
    }

    public function destroy(TruckModel $truckModel): RedirectResponse
    {
        $truckModel->delete();
        session()->flash('success', 'Модель успешно удалена!');
        return redirect()->route('admin.truck-models.index');
    }
}
