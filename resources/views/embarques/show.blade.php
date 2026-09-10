@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-3 animate-fade-in-up">
            <div>
                <h3 class="mb-0">Embarque {{ $shipment->shipment_number }}</h3>
                <p class="text-muted mb-0 small">{{ $shipment->originWarehouse->name }} &rarr; {{ $shipment->destinationStore->name }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('embarques.index') }}" class="btn btn-outline-secondary">Volver</a>
                @if ($shipment->status === \App\Models\Shipment::STATUS_PREPARADO)
                    <form method="POST" action="{{ route('embarques.cargar', $shipment) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-primary" onclick="return confirm('Confirmar la carga del vehiculo?')">Cargar vehiculo</button>
                    </form>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.1s;">
            <div class="card-header"><strong>Datos del embarque</strong></div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <strong>Estado:</strong>
                    @include('partials.order_status', ['status' => $shipment->status])
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Vehiculo:</strong> {{ $shipment->vehicle?->code ?? '—' }} ({{ $shipment->vehicle?->plate ?? '—' }})</p>
                        <p class="mb-0"><strong>Conductor:</strong> {{ $shipment->driver_name ?? $shipment->vehicle?->driver_name ?? '—' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Origen:</strong> {{ $shipment->originWarehouse->name }}</p>
                        <p class="mb-0"><strong>Destino:</strong> {{ $shipment->destinationStore->name }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Programado:</strong> {{ $shipment->scheduled_at?->format('d/m/Y H:i') ?? '—' }}</p>
                        <p class="mb-0"><strong>Pedidos:</strong> {{ $shipment->orders->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.15s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Pedidos del embarque</strong>
                <span class="badge bg-primary">{{ $shipment->orders->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Estado</th>
                            <th>Preparado el</th>
                            <th class="text-center">Total preparado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($shipment->orders as $order)
                            <tr class="stagger-child">
                                <td><a href="{{ route('almacen.pedidos.show', $order) }}" class="text-decoration-none fw-bold">{{ $order->order_number }}</a></td>
                                <td>@include('partials.order_status', ['status' => $order->status])</td>
                                <td class="text-muted small">{{ $order->prepared_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="text-center">{{ $order->items->sum('quantity_prepared') }} pzas</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <p class="mb-0">Sin pedidos asignados.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card animate-fade-in-up" style="animation-delay:0.2s;">
            <div class="card-header"><strong>Historial del embarque</strong></div>
            <div class="card-body">
                @forelse ($shipment->movements->sortBy('created_at') as $movement)
                    <div class="timeline-item d-flex gap-3">
                        <div class="timeline-dot flex-shrink-0">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                style="width:32px;height:32px;font-size:0.75rem;font-weight:700;">
                                {{ $loop->iteration }}
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold">{{ $movement->action }}</div>
                            <div class="text-muted small">
                                {{ $movement->created_at->format('d/m/Y H:i') }} &middot;
                                {{ $movement->user?->name ?? 'sistema' }} &middot;
                                Estado: <span class="text-capitalize">{{ str_replace('_', ' ', $movement->state) }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-state-icon">&#8982;</div>
                        <p class="mb-0">Sin movimientos registrados.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
