@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-3 animate-fade-in-up">
            <div>
                <h3 class="mb-0">Pedido {{ $order->order_number }}</h3>
                <span class="text-muted small">Creado el {{ $order->ordered_at->format('d/m/Y H:i') }} por {{ $order->creator->name }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                @include('partials.order_status', ['status' => $order->status])
                <a href="{{ route('ventas.pedidos') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row g-3">
            <div class="col-md-8">
                <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.1s;">
                    <div class="card-header"><strong>Productos</strong></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Presentacion</th>
                                    <th>Sabor</th>
                                    <th class="text-center">Pedido</th>
                                    <th class="text-center">Recibido</th>
                                    <th class="text-center">Preparado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr class="stagger-child">
                                        <td>
                                            {{ ucfirst($item->presentation->presentation_type) }}
                                            <span class="text-muted">({{ $item->presentation->sku }})</span>
                                        </td>
                                        <td>{{ $item->presentation->flavor ?? '—' }}</td>
                                        <td class="text-center fw-bold">{{ $item->quantity_requested }}</td>
                                        <td class="text-center">{{ $item->quantity_received ?? '—' }}</td>
                                        <td class="text-center">{{ $item->quantity_prepared ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($order->notes)
                    <div class="alert alert-light border animate-fade-in-up" style="animation-delay:0.15s;">
                        <strong>Notas:</strong> {{ $order->notes }}
                    </div>
                @endif
            </div>

            <div class="col-md-4">
                <div class="card mb-3 animate-slide-in" style="animation-delay:0.1s;">
                    <div class="card-header"><strong>Informacion</strong></div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Tienda:</strong> {{ $order->store->name }} ({{ $order->store->code }})</p>
                        <p class="mb-1"><strong>Almacen:</strong> {{ $order->warehouse?->name ?? '—' }}</p>
                        <p class="mb-0"><strong>Estado:</strong> {{ str_replace('_', ' ', $order->status) }}</p>
                    </div>
                </div>

                <div class="card mb-3 animate-slide-in" style="animation-delay:0.2s;">
                    <div class="card-header"><strong>Codigo QR del pedido</strong></div>
                    <div class="card-body text-center">
                        <div class="animate-scale-in">
                            <img src="{{ $qr }}" alt="QR del pedido" width="180" class="rounded"
                                style="box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                        </div>
                        <p class="text-muted small mb-0 mt-2">Escanea este codigo en Trazabilidad para localizar el pedido.</p>
                    </div>
                </div>

                <div class="card animate-slide-in" style="animation-delay:0.3s;">
                    <div class="card-header"><strong>Historial</strong></div>
                    <ul class="list-group list-group-flush small">
                        @forelse ($order->movements->sortByDesc('created_at') as $movement)
                            <li class="list-group-item stagger-child">
                                <div class="fw-bold">{{ $movement->action }}</div>
                                <div class="text-muted">
                                    {{ $movement->created_at->format('d/m/Y H:i') }} &middot; {{ $movement->user?->name ?? 'sistema' }}
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item">
                                <div class="empty-state">
                                    <p class="mb-0">Sin movimientos registrados.</p>
                                </div>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
