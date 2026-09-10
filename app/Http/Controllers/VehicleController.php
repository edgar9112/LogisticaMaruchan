<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleRequest;
use App\Models\Vehicle;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $vehicles = Vehicle::query()
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('plate', 'like', "%{$search}%")
                        ->orWhere('driver_name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('active')
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return view('vehiculos.index', compact('vehicles'));
    }

    public function create()
    {
        return view('vehiculos.create');
    }

    public function store(VehicleRequest $request)
    {
        Vehicle::create($request->validated());

        return redirect()
            ->route('vehiculos.index')
            ->with('success', 'Vehículo registrado correctamente.');
    }

    public function edit(Vehicle $vehicle)
    {
        return view('vehiculos.edit', compact('vehicle'));
    }

    public function update(VehicleRequest $request, Vehicle $vehicle)
    {
        $vehicle->update($request->validated());

        return redirect()
            ->route('vehiculos.index')
            ->with('success', 'Vehículo actualizado correctamente.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->update(['active' => false]);

        return redirect()
            ->route('vehiculos.index')
            ->with('success', 'Vehículo desactivado correctamente.');
    }
}
