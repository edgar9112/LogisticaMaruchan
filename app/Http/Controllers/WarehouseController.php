<?php

namespace App\Http\Controllers;

use App\Http\Requests\WarehouseRequest;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $warehouses = Warehouse::withCount('locations')
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('almacenes.index', compact('warehouses'));
    }

    public function create()
    {
        return view('almacenes.create');
    }

    public function store(WarehouseRequest $request)
    {
        Warehouse::create($request->validated());

        return redirect()
            ->route('almacenes.index')
            ->with('success', 'Almacén creado correctamente.');
    }

    public function edit(Warehouse $warehouse)
    {
        $warehouse->load('locations');

        return view('almacenes.edit', compact('warehouse'));
    }

    public function update(WarehouseRequest $request, Warehouse $warehouse)
    {
        $warehouse->update($request->validated());

        return redirect()
            ->route('almacenes.edit', $warehouse)
            ->with('success', 'Almacén actualizado correctamente.');
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->update(['active' => false]);

        return redirect()
            ->route('almacenes.index')
            ->with('success', 'Almacén desactivado correctamente.');
    }

    public function storeLocation(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $warehouse->locations()->create($validated);

        return back()->with('success', 'Ubicación agregada correctamente.');
    }

    public function destroyLocation(WarehouseLocation $location)
    {
        $location->delete();

        return back()->with('success', 'Ubicación eliminada correctamente.');
    }
}