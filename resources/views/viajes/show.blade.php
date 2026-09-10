@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-3 animate-fade-in-up">
            <div>
                <h3 class="mb-0">Viaje {{ $shipment->shipment_number }}</h3>
                <p class="text-muted mb-0 small">{{ $shipment->originWarehouse->name }} &rarr; {{ $shipment->destinationStore->name }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('viajes.index') }}" class="btn btn-outline-secondary">Volver</a>
                @if ($shipment->status === \App\Models\Shipment::STATUS_CARGADO)
                    <form method="POST" action="{{ route('viajes.salir', $shipment) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-primary" onclick="return confirm('Iniciar el viaje? El vehiculo se marcara EN_TRANSITO.')">Salir a ruta</button>
                    </form>
                @elseif ($shipment->status === \App\Models\Shipment::STATUS_EN_TRANSITO)
                    <form method="POST" action="{{ route('viajes.llegar', $shipment) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-success" onclick="return confirm('Confirmar la entrega en tienda?')">Llegada a tienda</button>
                    </form>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.1s;">
            <div class="card-header"><strong>Datos del viaje</strong></div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <strong>Estado:</strong>
                    @if ($shipment->status === \App\Models\Shipment::STATUS_EN_TRANSITO)
                        <span class="badge badge-pulse text-bg-primary">{{ $shipment->status }}</span>
                    @elseif ($shipment->status === \App\Models\Shipment::STATUS_CARGADO)
                        <span class="badge bg-warning text-dark">{{ $shipment->status }}</span>
                    @else
                        <span class="badge bg-secondary">{{ $shipment->status }}</span>
                    @endif
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
                        <p class="mb-1"><strong>Salida:</strong> {{ $shipment->departed_at?->format('d/m/Y H:i') ?? '—' }}</p>
                        <p class="mb-0"><strong>Llegada:</strong> {{ $shipment->arrived_at?->format('d/m/Y H:i') ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.15s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Pedidos del viaje</strong>
                <span class="badge bg-primary">{{ $shipment->orders->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Estado</th>
                            <th>Tienda</th>
                            <th class="text-center">Total despachado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($shipment->orders as $order)
                            <tr class="stagger-child">
                                <td><strong>{{ $order->order_number }}</strong></td>
                                <td>@include('partials.order_status', ['status' => $order->status])</td>
                                <td>{{ $order->store->name }}</td>
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

        @if ($shipment->status === \App\Models\Shipment::STATUS_EN_TRANSITO)
            <div class="card mb-3 border-warning animate-fade-in-up" style="animation-delay:0.2s;">
                <div class="card-header" style="background:#fef3c7;color:#92400e;border:none;"><strong>&#9888; Registrar incidencia en ruta</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('viajes.incidencia', $shipment) }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Tipo de incidencia</label>
                                <select name="type" id="type" class="form-select" required>
                                    <option value="">Selecciona...</option>
                                    @foreach (\App\Models\Incident::ROUTE_INCIDENT_TYPES as $value => $label)
                                        <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Fecha y hora</label>
                                <input type="datetime-local" name="occurred_at" id="occurred_at" class="form-control" value="{{ old('occurred_at') ?? now()->format('Y-m-d\TH:i') }}">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button class="btn btn-warning w-100">Registrar incidencia</button>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Descripcion</label>
                                <textarea name="description" id="description" class="form-control" rows="2" required maxlength="1000" placeholder="Describe la situacion: desperfecto, parada, retraso, etc.">{{ old('description') }}</textarea>
                                @error('description')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.25s;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Incidencias en ruta</strong>
                <span class="badge bg-danger">{{ $shipment->incidents->count() }}</span>
            </div>
            <div class="card-body">
                @forelse ($shipment->incidents->sortByDesc('occurred_at') as $incident)
                    <div class="timeline-item d-flex gap-3">
                        <div class="flex-shrink-0">
                            <span class="badge rounded-pill bg-{{ $incident->type === \App\Models\Incident::TYPE_PERCANCE ? 'danger' : ($incident->type === \App\Models\Incident::TYPE_RETRASO ? 'warning' : 'secondary') }}">{{ $incident->type_label }}</span>
                        </div>
                        <div>
                            <div class="fw-bold">{{ $incident->description }}</div>
                            <div class="text-muted small">
                                {{ $incident->occurred_at->format('d/m/Y H:i') }} &middot;
                                {{ $incident->user?->name ?? 'sistema' }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <p class="mb-0">Sin incidencias registradas.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="card animate-fade-in-up" style="animation-delay:0.3s;">
            <div class="card-header"><strong>Historial del viaje</strong></div>
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
                            @if ($movement->description)
                                <div class="small mt-1">{{ $movement->description }}</div>
                            @endif
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
