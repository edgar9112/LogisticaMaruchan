@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-3 animate-fade-in-up">
            <div class="page-header mb-0">
                <h3 class="mb-0">Vehiculos</h3>
            </div>
            <a href="{{ route('vehiculos.create') }}" class="btn btn-success">+ Nuevo vehiculo</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.1s;">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('vehiculos.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <div class="search-wrapper">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                                placeholder="Buscar por codigo, placa o conductor...">
                        </div>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary">Buscar</button>
                    </div>
                    @if (request('search'))
                        <div class="col-auto">
                            <a href="{{ route('vehiculos.index') }}" class="btn btn-outline-secondary">Limpiar</a>
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
                            <th>Placa</th>
                            <th>Conductor</th>
                            <th class="text-center">Capacidad</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vehicles as $vehicle)
                            <tr class="stagger-child">
                                <td><code>{{ $vehicle->code }}</code></td>
                                <td class="fw-medium">{{ $vehicle->plate ?? '—' }}</td>
                                <td>{{ $vehicle->driver_name ?? '—' }}</td>
                                <td class="text-center">{{ $vehicle->capacity ? number_format($vehicle->capacity, 0) . ' kg' : '—' }}</td>
                                <td>
                                    @if ($vehicle->active)
                                        <span class="badge badge-pulse" style="background:#dcfce7;color:#166534;">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('vehiculos.edit', $vehicle) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    @if ($vehicle->active)
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-1"
                                            onclick="event.preventDefault(); if(confirm('Desactivar este vehiculo?')) document.getElementById('deactivate-{{ $vehicle->id }}').submit();">
                                            Desactivar
                                        </button>
                                        <form id="deactivate-{{ $vehicle->id }}" method="POST" action="{{ route('vehiculos.destroy', $vehicle) }}" class="d-none">
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
                                        <div class="empty-state-icon">&#9650;</div>
                                        <p class="mb-0">No hay vehiculos registrados.</p>
                                        <a href="{{ route('vehiculos.create') }}" class="btn btn-success btn-sm mt-2">Agregar vehiculo</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($vehicles->hasPages())
                <div class="card-footer d-flex justify-content-center">
                    {{ $vehicles->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
