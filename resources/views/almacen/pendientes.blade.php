@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="page-header animate-fade-in-up">
            <h3 class="mb-1">Almacen &mdash; Recepcion</h3>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="card mb-4 animate-fade-in-up" style="animation-delay:0.1s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Pedidos por recibir</strong>
                <span class="badge bg-warning text-dark">{{ $pending->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Tienda</th>
                            <th>Estado</th>
                            <th>Pedido el</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pending as $order)
                            <tr class="stagger-child">
                                <td><strong>{{ $order->order_number }}</strong></td>
                                <td>{{ $order->store->name }}</td>
                                <td>@include('partials.order_status', ['status' => $order->status])</td>
                                <td class="text-muted small">{{ $order->ordered_at->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('almacen.pedidos.show', $order) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                    @if (in_array($order->status, ['CREADO', 'CONFIRMADO']))
                                        <a href="{{ route('almacen.pedidos.recibir', $order) }}" class="btn btn-sm btn-primary ms-1">Recibir</a>
                                    @elseif ($order->status === 'EN_ALMACEN')
                                        <form method="POST" action="{{ route('almacen.pedidos.confirmar', $order) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-success ms-1" onclick="return confirm('Confirmar la verificacion de entrada?')">Confirmar recepcion</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">&#9881;</div>
                                        <p class="mb-0">No hay pedidos pendientes de recepcion.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card animate-fade-in-up" style="animation-delay:0.15s;">
            <div class="card-header"><strong>Recientemente recibidos</strong></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Tienda</th>
                            <th>Estado</th>
                            <th>Recibido el</th>
                            <th class="text-end">Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recent as $order)
                            <tr class="stagger-child">
                                <td><strong>{{ $order->order_number }}</strong></td>
                                <td>{{ $order->store->name }}</td>
                                <td>@include('partials.order_status', ['status' => $order->status])</td>
                                <td class="text-muted small">{{ $order->received_at?->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('almacen.pedidos.show', $order) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                    @if ($order->status === 'RECIBIDO')
                                        <a href="{{ route('almacen.pedidos.clasificar', $order) }}" class="btn btn-sm btn-warning ms-1">Clasificar</a>
                                    @elseif ($order->status === 'CLASIFICADO')
                                        <a href="{{ route('almacen.pedidos.preparar', $order) }}" class="btn btn-sm btn-info ms-1">Preparar</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <p class="mb-0">Aun no hay recepciones.</p>
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
