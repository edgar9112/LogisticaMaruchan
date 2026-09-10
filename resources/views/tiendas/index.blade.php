@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-3 animate-fade-in-up">
            <div class="page-header mb-0">
                <h3 class="mb-0">Tiendas</h3>
            </div>
            <a href="{{ route('tiendas.create') }}" class="btn btn-success">+ Nueva tienda</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.1s;">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('tiendas.index') }}" class="row g-2 align-items-end">
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
                            <a href="{{ route('tiendas.index') }}" class="btn btn-outline-secondary">Limpiar</a>
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
                            <th>Telefono</th>
                            <th>Contacto</th>
                            <th>Horario</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stores as $store)
                            <tr class="stagger-child">
                                <td><code>{{ $store->code }}</code></td>
                                <td class="fw-medium">{{ $store->name }}</td>
                                <td class="text-muted">{{ $store->address ?? '—' }}</td>
                                <td>{{ $store->phone ?? '—' }}</td>
                                <td>{{ $store->contact_name ?? '—' }}</td>
                                <td class="small">{{ $store->opening_hours ?? '—' }}</td>
                                <td>
                                    @if ($store->active)
                                        <span class="badge badge-pulse" style="background:#dcfce7;color:#166534;">Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Inactiva</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('tiendas.edit', $store) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    @if ($store->active)
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-1"
                                            data-confirm="Desactivar esta tienda?"
                                            onclick="event.preventDefault(); if(confirm(this.dataset.confirm)) document.getElementById('deactivate-{{ $store->id }}').submit();">
                                            Desactivar
                                        </button>
                                        <form id="deactivate-{{ $store->id }}" method="POST" action="{{ route('tiendas.destroy', $store) }}" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">&#9962;</div>
                                        <p class="mb-0">No hay tiendas registradas.</p>
                                        <a href="{{ route('tiendas.create') }}" class="btn btn-success btn-sm mt-2">Agregar tienda</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($stores->hasPages())
                <div class="card-footer d-flex justify-content-center">
                    {{ $stores->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
