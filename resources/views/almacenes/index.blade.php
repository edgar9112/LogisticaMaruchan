@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-3 animate-fade-in-up">
            <div class="page-header mb-0">
                <h3 class="mb-0">Almacenes</h3>
            </div>
            <a href="{{ route('almacenes.create') }}" class="btn btn-success">+ Nuevo almacen</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.1s;">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('almacenes.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <div class="search-wrapper">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                                placeholder="Buscar por codigo, nombre o direccion...">
                        </div>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary">Buscar</button>
                    </div>
                    @if (request('search'))
                        <div class="col-auto">
                            <a href="{{ route('almacenes.index') }}" class="btn btn-outline-secondary">Limpiar</a>
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
                            <th>Codigo</th>
                            <th>Nombre</th>
                            <th>Direccion</th>
                            <th class="text-center">Ubicaciones</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($warehouses as $warehouse)
                            <tr class="stagger-child">
                                <td><code>{{ $warehouse->code }}</code></td>
                                <td class="fw-medium">{{ $warehouse->name }}</td>
                                <td class="text-muted">{{ $warehouse->address ?? '—' }}</td>
                                <td class="text-center">{{ $warehouse->locations_count }}</td>
                                <td>
                                    @if ($warehouse->active)
                                        <span class="badge badge-pulse" style="background:#dcfce7;color:#166534;">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('almacenes.edit', $warehouse) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    @if ($warehouse->active)
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-1"
                                            onclick="event.preventDefault(); if(confirm('Desactivar este almacen?')) document.getElementById('deactivate-{{ $warehouse->id }}').submit();">
                                            Desactivar
                                        </button>
                                        <form id="deactivate-{{ $warehouse->id }}" method="POST" action="{{ route('almacenes.destroy', $warehouse) }}" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">&#9878;</div>
                                        <p class="mb-0">No hay almacenes registrados.</p>
                                        <a href="{{ route('almacenes.create') }}" class="btn btn-success btn-sm mt-2">Agregar almacen</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($warehouses->hasPages())
                <div class="card-footer d-flex justify-content-center">
                    {{ $warehouses->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
