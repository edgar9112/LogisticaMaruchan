@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="page-header animate-fade-in-up">
            <h3 class="mb-1">Clasificar pedido</h3>
            <p class="text-muted mb-0">Registra la ubicacion fisica donde se guarda la mercancia de este pedido.</p>
        </div>

        <div class="card mb-4 animate-fade-in-up" style="animation-delay:0.1s;">
            <div class="card-header" style="background:#fef3c7;color:#92400e;border:none;">
                <strong>{{ $order->order_number }}</strong> &mdash; {{ $order->store->name }}
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Estado:</strong> @include('partials.order_status', ['status' => $order->status])</p>
                <p class="mb-1"><strong>Almacen:</strong> {{ $order->warehouse?->name ?? '—' }}</p>
                <p class="mb-0"><strong>Recibido:</strong> {{ $order->received_at?->format('d/m/Y H:i') ?? '—' }}</p>
            </div>
        </div>

        <div class="card mb-4 animate-fade-in-up" style="animation-delay:0.15s;">
            <div class="card-header"><strong>Productos del pedido</strong></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Presentacion</th>
                            <th class="text-center">Pedidas</th>
                            <th class="text-center">Recibidas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr class="stagger-child">
                                <td>{{ ucfirst($item->presentation->presentation_type) }} &middot; {{ $item->presentation->flavor }}</td>
                                <td class="text-center fw-bold">{{ $item->quantity_requested }}</td>
                                <td class="text-center">{{ $item->quantity_received ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card animate-fade-in-up" style="animation-delay:0.2s;">
            <div class="card-header"><strong>Ubicacion en almacen</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('almacen.pedidos.clasificar.guardar', $order) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-medium">Zona / ubicacion de almacenaje</label>
                        <input type="text" name="location" class="form-control"
                            value="{{ old('location') }}"
                            placeholder="Ej. Zona A, Pasillo 3, Repisa 2-A"
                            required autofocus>
                        <div class="form-text">Describe la ubicacion fisica donde quedara esta mercancia.</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('almacen.pendientes') }}" class="btn btn-outline-secondary">Cancelar</a>
                        <button class="btn btn-warning">Clasificar y ubicar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
