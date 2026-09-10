<?php

namespace App\Http\Controllers;

use App\Http\Requests\PresentationRequest;
use App\Models\Presentation;

class PresentationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $presentations = Presentation::query()
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('presentation_type', 'like', "%{$search}%")
                        ->orWhere('flavor', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->orderBy('presentation_type')
            ->orderBy('flavor')
            ->paginate(10)
            ->withQueryString();

        return view('presentaciones.index', compact('presentations'));
    }

    public function create()
    {
        return view('presentaciones.create');
    }

    public function store(PresentationRequest $request)
    {
        Presentation::create($request->validated());

        return redirect()
            ->route('presentaciones.index')
            ->with('success', 'Presentación creada correctamente.');
    }

    public function edit(Presentation $presentation)
    {
        return view('presentaciones.edit', compact('presentation'));
    }

    public function update(PresentationRequest $request, Presentation $presentation)
    {
        $presentation->update($request->validated());

        return redirect()
            ->route('presentaciones.index')
            ->with('success', 'Presentación actualizada correctamente.');
    }

    public function destroy(Presentation $presentation)
    {
        $presentation->update(['active' => false]);

        return redirect()
            ->route('presentaciones.index')
            ->with('success', 'Presentación desactivada correctamente.');
    }
}