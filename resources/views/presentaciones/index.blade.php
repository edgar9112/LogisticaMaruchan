@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-3 animate-fade-in-up">
            <div class="page-header mb-0">
                <h3 class="mb-0">Catalogo de presentaciones</h3>
            </div>
            <a href="{{ route('presentaciones.create') }}" class="btn btn-success">+ Nueva presentacion</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.1s;">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('presentaciones.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <div class="search-wrapper">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                                placeholder="Buscar por tipo, sabor o SKU...">
                        </div>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary">Buscar</button>
                    </div>
                    @if (request('search'))
                        <div class="col-auto">
                            <a href="{{ route('presentaciones.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <div class="card animate-fade-in-up" style="animation-delay:0.15s;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Presentacion</th>
                            <th>Tipo</th>
                            <th>Sabor</th>
                            <th class="text-center">Piezas/caja</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($presentations as $presentation)
                            <tr class="stagger-child">
                                <td><code>{{ $presentation->sku }}</code></td>
                                <td class="fw-medium">{{ $presentation->name }}</td>
                                <td>{{ ucfirst($presentation->presentation_type) }}</td>
                                <td>{{ $presentation->flavor ?? '—' }}</td>
                                <td class="text-center">{{ $presentation->pieces_per_box ?? '—' }}</td>
                                <td>
                                    @if ($presentation->active)
                                        <span class="badge badge-pulse" style="background:#dcfce7;color:#166534;">Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Inactiva</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('presentaciones.edit', $presentation) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    @if ($presentation->active)
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-1"
                                            onclick="event.preventDefault(); if(confirm('Desactivar esta presentacion?')) document.getElementById('deactivate-{{ $presentation->id }}').submit();">
                                            Desactivar
                                        </button>
                                        <form id="deactivate-{{ $presentation->id }}" method="POST" action="{{ route('presentaciones.destroy', $presentation) }}" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">&#9733;</div>
                                        <p class="mb-0">No hay presentaciones registradas.</p>
                                        <a href="{{ route('presentaciones.create') }}" class="btn btn-success btn-sm mt-2">Agregar presentacion</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($presentations->hasPages())
                <div class="card-footer d-flex justify-content-center">
                    {{ $presentations->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
