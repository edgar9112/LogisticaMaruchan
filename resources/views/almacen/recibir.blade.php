@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-3 animate-fade-in-up">
            <div>
                <h3 class="mb-0">Recibir pedido {{ $order->order_number }}</h3>
                <span class="text-muted small">{{ $order->store->name }} &mdash; pedido el {{ $order->ordered_at->format('d/m/Y H:i') }}</span>
            </div>
            <a href="{{ route('almacen.pendientes') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
        </div>

        <form method="POST" action="{{ route('almacen.pedidos.entrada', $order) }}">
            @csrf

            <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.1s;">
                <div class="card-header"><strong>Registrar cantidades recibidas</strong></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Presentacion</th>
                                <th class="text-center">Pedidas</th>
                                <th class="text-center">Recibidas *</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr class="stagger-child">
                                    <td>
                                        {{ ucfirst($item->presentation->presentation_type) }} &middot; {{ $item->presentation->flavor }}
                                        <span class="text-muted">({{ $item->presentation->sku }})</span>
                                    </td>
                                    <td class="text-center fw-bold">{{ $item->quantity_requested }}</td>
                                    <td class="text-center" style="width:160px">
                                        <input type="number" name="quantities[{{ $item->id }}]" min="0"
                                            class="form-control form-control-sm text-center"
                                            value="{{ old('quantities.' . $item->id, $item->quantity_requested) }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @error('quantities.*')<div class="text-danger px-3 pb-2 small">{{ $message }}</div>@enderror
            </div>

            <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.15s;">
                <div class="card-header"><strong>Incidencia (opcional)</strong></div>
                <div class="card-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Tipo de incidencia</label>
                        <select name="incident_type" class="form-select">
                            <option value="">Sin incidencia</option>
                            @foreach (['faltante', 'sobrante', 'danado', 'diferencia_de_cantidad', 'mercancia_no_localizada'] as $type)
                                <option value="{{ $type }}" @selected(old('incident_type') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-medium">Descripcion</label>
                        <input type="text" name="incident_description" class="form-control"
                            value="{{ old('incident_description') }}" placeholder="Detalles de la novedad">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('almacen.pendientes') }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Registrar entrada</button>
            </div>
        </form>
    </div>
@endsection
