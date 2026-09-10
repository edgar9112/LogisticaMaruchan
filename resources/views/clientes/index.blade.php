@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-3 animate-fade-in-up">
            <div class="page-header mb-0">
                <h3 class="mb-0">Clientes</h3>
            </div>
            <a href="{{ route('clientes.create') }}" class="btn btn-success">+ Nuevo cliente</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.1s;">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('clientes.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <div class="search-wrapper">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                                placeholder="Buscar por nombre, correo, telefono o direccion...">
                        </div>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary">Buscar</button>
                    </div>
                    @if (request('search'))
                        <div class="col-auto">
                            <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">Limpiar</a>
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
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Telefono</th>
                            <th>Direccion</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            <tr class="stagger-child">
                                <td class="fw-medium">{{ $customer->name }}</td>
                                <td class="text-muted">{{ $customer->email ?? '—' }}</td>
                                <td>{{ $customer->phone ?? '—' }}</td>
                                <td class="text-muted small">{{ $customer->address ?? '—' }}</td>
                                <td>
                                    @if ($customer->active)
                                        <span class="badge badge-pulse" style="background:#dcfce7;color:#166534;">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('clientes.edit', $customer) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    @if ($customer->active)
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-1"
                                            onclick="event.preventDefault(); if(confirm('Desactivar este cliente?')) document.getElementById('deactivate-{{ $customer->id }}').submit();">
                                            Desactivar
                                        </button>
                                        <form id="deactivate-{{ $customer->id }}" method="POST" action="{{ route('clientes.destroy', $customer) }}" class="d-none">
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
                                        <div class="empty-state-icon">&#9823;</div>
                                        <p class="mb-0">No hay clientes registrados.</p>
                                        <a href="{{ route('clientes.create') }}" class="btn btn-success btn-sm mt-2">Agregar cliente</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($customers->hasPages())
                <div class="card-footer d-flex justify-content-center">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
