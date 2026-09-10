@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="page-header animate-fade-in-up">
            <h3 class="mb-1">Viajes de transporte</h3>
            <p class="text-muted mb-0">Seguimiento de los traslados hacia las tiendas.</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="card mb-4 animate-fade-in-up" style="animation-delay:0.1s;">
            <div class="card-header d-flex align-items-center justify-content-between" style="background:linear-gradient(135deg,#1e40af,#2563eb);color:#fff;border:none;">
                <strong>Viajes activos</strong>
                <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;">{{ $active->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Embarque</th>
                            <th>Ruta</th>
                            <th>Vehiculo</th>
                            <th>Conductor</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($active as $shipment)
                            <tr class="stagger-child">
                                <td><strong>{{ $shipment->shipment_number }}</strong></td>
                                <td>
                                    <span class="text-muted">{{ $shipment->originWarehouse->name }}</span>
                                    <span class="mx-1">&rarr;</span>
                                    <span class="fw-medium">{{ $shipment->destinationStore->name }}</span>
                                </td>
                                <td>{{ $shipment->vehicle?->code ?? '—' }}</td>
                                <td>{{ $shipment->driver_name ?? $shipment->vehicle?->driver_name ?? '—' }}</td>
                                <td>
                                    @if ($shipment->status === \App\Models\Shipment::STATUS_EN_TRANSITO)
                                        <span class="badge badge-pulse" style="background:#dbeafe;color:#1e40af;">{{ $shipment->status }}</span>
                                    @else
                                        @include('partials.order_status', ['status' => $shipment->status])
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('viajes.show', $shipment) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">&#9654;</div>
                                        <p class="mb-0">No hay viajes activos.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card animate-fade-in-up" style="animation-delay:0.2s;">
            <div class="card-header"><strong>Historial de viajes</strong></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Embarque</th>
                            <th>Destino</th>
                            <th>Vehiculo</th>
                            <th>Llego</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($history as $shipment)
                            <tr class="stagger-child">
                                <td><strong>{{ $shipment->shipment_number }}</strong></td>
                                <td>{{ $shipment->destinationStore->name }}</td>
                                <td>{{ $shipment->vehicle?->code ?? '—' }}</td>
                                <td class="text-muted small">{{ $shipment->arrived_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>@include('partials.order_status', ['status' => $shipment->status])</td>
                                <td class="text-end">
                                    <a href="{{ route('viajes.show', $shipment) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">&#8982;</div>
                                        <p class="mb-0">Sin viajes concluidos.</p>
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
