<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PartType\StoreRequest;
use App\Http\Requests\Admin\PartType\UpdateRequest;
use App\Models\PartType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PartTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = PartType::query();

        if ($request->has('search') && $request->search != null) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
        }

        $partTypes = $query->paginate(10);
        return view('admin.partType.index', compact('partTypes'));
    }

    public function create(): View
    {
        return view('admin.partType.create');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        PartType::create($data);
        session()->flash('success', 'Тип успешно добавлен!');
        return redirect()->route('admin.part-types.create');
    }

    public function edit(PartType $partType): View
    {
        return view('admin.partType.edit', compact('partType'));
    }

    public function update(PartType $partType, UpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $partType->update($data);
        session()->flash('success', 'Тип успешно обновлен!');
        return redirect()->route('admin.part-types.index');
    }

    public function destroy(PartType $partType): RedirectResponse
    {
        $partType->delete();
        session()->flash('success', 'Тип успешно удален!');
        return redirect()->route('admin.part-types.index');
    }
}
