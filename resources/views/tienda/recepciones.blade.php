@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="page-header animate-fade-in-up">
            <h3 class="mb-1">Recepcion en tienda</h3>
            <p class="text-muted mb-0">Confirma la llegada de tus pedidos para cerrar el flujo logistico.</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="card mb-4 animate-fade-in-up" style="animation-delay:0.1s;">
            <div class="card-header d-flex align-items-center justify-content-between" style="background:linear-gradient(135deg,#1e40af,#2563eb);color:#fff;border:none;">
                <strong>Pedidos entregados &mdash; pendientes de confirmar</strong>
                <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;">{{ $pendingConfirmation->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Tienda</th>
                            <th>Estado</th>
                            <th class="text-center">Articulos</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingConfirmation as $order)
                            <tr class="stagger-child">
                                <td><strong>{{ $order->order_number }}</strong></td>
                                <td>{{ $order->store->name }}</td>
                                <td>@include('partials.order_status', ['status' => $order->status])</td>
                                <td class="text-center">{{ $order->items->sum('quantity_prepared') }} pzas</td>
                                <td class="text-end">
                                    <a href="{{ route('devoluciones.create', $order) }}" class="btn btn-sm btn-outline-danger">Devolver</a>
                                    <form method="POST" action="{{ route('tienda.recepciones.confirmar', $order) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success" onclick="return confirm('Confirmar la recepcion del pedido {{ $order->order_number }}? Se cerrara el ciclo.')">Recibido &mdash; cerrar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">&#10003;</div>
                                        <p class="mb-0">No hay pedidos entregados pendientes de confirmar.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card animate-fade-in-up" style="animation-delay:0.15s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Pedidos cerrados recientemente</strong>
                <span class="badge bg-secondary">{{ $recentlyClosed->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Tienda</th>
                            <th>Estado</th>
                            <th>Recibido en tienda el</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentlyClosed as $order)
                            <tr class="stagger-child">
                                <td><strong>{{ $order->order_number }}</strong></td>
                                <td>{{ $order->store->name }}</td>
                                <td>@include('partials.order_status', ['status' => $order->status])</td>
                                <td class="text-muted small">{{ $order->updated_at->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('devoluciones.create', $order) }}" class="btn btn-sm btn-outline-danger">Devolver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <p class="mb-0">Aun no hay pedidos cerrados en esta tienda.</p>
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
