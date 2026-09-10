<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequest;
use App\Models\Store;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $stores = Store::query()
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

        return view('tiendas.index', compact('stores'));
    }

    public function create()
    {
        return view('tiendas.create');
    }

    public function store(StoreRequest $request)
    {
        Store::create($request->validated());

        return redirect()
            ->route('tiendas.index')
            ->with('success', 'Tienda creada correctamente.');
    }

    public function edit(Store $store)
    {
        return view('tiendas.edit', compact('store'));
    }

    public function update(StoreRequest $request, Store $store)
    {
        $store->update($request->validated());

        return redirect()
            ->route('tiendas.index')
            ->with('success', 'Tienda actualizada correctamente.');
    }

    public function destroy(Store $store)
    {
        $store->update(['active' => false]);

        return redirect()
            ->route('tiendas.index')
            ->with('success', 'Tienda desactivada correctamente.');
    }
}