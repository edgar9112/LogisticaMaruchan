@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-3 animate-fade-in-up">
            <div class="page-header mb-0">
                <h3 class="mb-0">Embarques</h3>
                <p class="text-muted mb-0 small">Planificacion de traslados hacia las tiendas.</p>
            </div>
            <a href="{{ route('embarques.create') }}" class="btn btn-primary">Nuevo embarque</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="card animate-fade-in-up" style="animation-delay:0.1s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Embarques registrados</strong>
                <span class="badge bg-primary">{{ $shipments->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Num. embarque</th>
                            <th>Destino</th>
                            <th>Vehiculo</th>
                            <th>Conductor</th>
                            <th class="text-center">Pedidos</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($shipments as $shipment)
                            <tr class="stagger-child">
                                <td><strong>{{ $shipment->shipment_number }}</strong></td>
                                <td>{{ $shipment->destinationStore->name }}</td>
                                <td>{{ $shipment->vehicle?->code ?? '—' }}</td>
                                <td>{{ $shipment->driver_name ?? $shipment->vehicle?->driver_name ?? '—' }}</td>
                                <td class="text-center">{{ $shipment->orders->count() }}</td>
                                <td>@include('partials.order_status', ['status' => $shipment->status])</td>
                                <td class="text-end">
                                    <a href="{{ route('embarques.show', $shipment) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">&#9654;</div>
                                        <p class="mb-0">No hay embarques registrados.</p>
                                        <a href="{{ route('embarques.create') }}" class="btn btn-primary btn-sm mt-2">Crear embarque</a>
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
