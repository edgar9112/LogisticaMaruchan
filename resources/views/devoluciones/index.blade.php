@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-3 animate-fade-in-up">
            <div class="page-header mb-0">
                <h3 class="mb-0">Devoluciones</h3>
                <p class="text-muted mb-0 small">Pedidos que las tiendas devuelven al almacen, con motivo y trazabilidad.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.1s;">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('devoluciones.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <select name="estado" class="form-select">
                            <option value="">Todos los estados</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected($activeStatus === $status)>{{ \App\Models\OrderReturn::STATUS_LABELS[$status] ?? $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary">Filtrar</button>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('devoluciones.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card animate-fade-in-up" style="animation-delay:0.15s;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Folio pedido</th>
                            <th>Tienda</th>
                            <th>Motivo</th>
                            <th class="text-center">Unidades</th>
                            <th>Estado</th>
                            <th>Solicitada el</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($returns as $devolucion)
                            <tr class="stagger-child">
                                <td><strong>{{ $devolucion->order->order_number }}</strong></td>
                                <td>{{ $devolucion->order->store->name }}</td>
                                <td>{{ $devolucion->reason_label }}</td>
                                <td class="text-center">{{ $devolucion->items->sum('quantity') }} pzas</td>
                                <td>@include('partials.return_status', ['status' => $devolucion->status])</td>
                                <td class="text-muted small">{{ $devolucion->requested_at->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('devoluciones.show', $devolucion) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">&#8634;</div>
                                        <p class="mb-0">No hay devoluciones registradas.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
