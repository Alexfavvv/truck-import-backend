<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TruckManufacturer\StoreRequest;
use App\Http\Requests\Admin\TruckManufacturer\UpdateRequest;
use App\Models\TruckManufacturer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TruckManufacturerController extends Controller
{
    public function index(Request $request)
    {
        $query = TruckManufacturer::query();

        if ($request->has('search') && $request->search != null) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
        }

        $truckManufacturers = $query->paginate(10);
        return view('admin.truckManufacturer.index', compact('truckManufacturers'));
    }

    public function create(): View
    {
        return view('admin.truckManufacturer.create');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        TruckManufacturer::create($data);
        session()->flash('success', 'Производитель успешно добавлен!');
        return redirect()->route('admin.truck-manufacturers.create');
    }

    public function edit(TruckManufacturer $truckManufacturer): View
    {
        return view('admin.truckManufacturer.edit', compact('truckManufacturer'));
    }

    public function update(TruckManufacturer $truckManufacturer, UpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $truckManufacturer->update($data);
        session()->flash('success', 'Производитель успешно обновлен!');
        return redirect()->route('admin.truck-manufacturers.index');
    }

    public function destroy(TruckManufacturer $truckManufacturer): RedirectResponse
    {
        $truckManufacturer->delete();
        session()->flash('success', 'Производитель успешно удален!');
        return redirect()->route('admin.truck-manufacturers.index');
    }
}
