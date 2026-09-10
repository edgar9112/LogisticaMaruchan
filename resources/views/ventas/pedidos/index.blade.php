@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-3 animate-fade-in-up">
            <div class="page-header mb-0">
                <h3 class="mb-0">Pedidos</h3>
            </div>
            <a href="{{ route('ventas.pedidos.create') }}" class="btn btn-success">+ Nuevo pedido</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.1s;">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('ventas.pedidos') }}" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <div class="search-wrapper">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                                placeholder="Buscar por folio o tienda...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Todos los estados</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>
                                    {{ str_replace('_', ' ', $status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary">Filtrar</button>
                    </div>
                    @if (request('search') || request('status'))
                        <div class="col-auto">
                            <a href="{{ route('ventas.pedidos') }}" class="btn btn-outline-secondary">Limpiar</a>
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
                            <th>Folio</th>
                            <th>Tienda</th>
                            <th class="text-center">Productos</th>
                            <th class="text-center">Cant. total</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th class="text-end">Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr class="stagger-child">
                                <td><strong>{{ $order->order_number }}</strong></td>
                                <td>{{ $order->store->name }}</td>
                                <td class="text-center">{{ $order->items_count }}</td>
                                <td class="text-center">{{ $order->items_sum_quantity_requested ?? 0 }}</td>
                                <td>@include('partials.order_status', ['status' => $order->status])</td>
                                <td class="text-muted small">{{ $order->ordered_at->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('ventas.pedidos.show', $order) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">&#9993;</div>
                                        <p class="mb-0">No hay pedidos registrados.</p>
                                        <a href="{{ route('ventas.pedidos.create') }}" class="btn btn-primary btn-sm mt-2">Crear primer pedido</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($orders->hasPages())
                <div class="card-footer d-flex justify-content-center">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
