@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="page-header animate-fade-in-up">
            <h3 class="mb-1">Registrar devolucion</h3>
            <p class="text-muted mb-0">Pedido <strong>{{ $order->order_number }}</strong> &mdash; {{ $order->store->name }}</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('devoluciones.store', $order) }}">
            @csrf

            <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.1s;">
                <div class="card-header"><strong>Motivo de la devolucion</strong></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Motivo</label>
                            <select name="reason_type" class="form-select @error('reason_type') is-invalid @enderror">
                                <option value="">Selecciona un motivo...</option>
                                @foreach ($reasons as $value => $label)
                                    <option value="{{ $value }}" @selected(old('reason_type') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Nota (opcional)</label>
                            <input type="text" name="note" value="{{ old('note') }}" maxlength="500" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.15s;">
                <div class="card-header"><strong>Productos a devolver</strong></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Recibido</th>
                                <th style="width:140px">Cantidad a devolver</th>
                                <th style="width:180px">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                @php
                                    $available = (int) $item->quantity_received ?: (int) $item->quantity_prepared;
                                @endphp
                                <tr class="stagger-child">
                                    <td>
                                        <strong>{{ $item->presentation->name }}</strong>
                                        <span class="text-muted d-block small">{{ $item->presentation->sku }}</span>
                                    </td>
                                    <td class="text-center">{{ $available }}</td>
                                    <td>
                                        <input type="number" name="items[{{ $item->id }}][quantity]"
                                            min="0" max="{{ $available }}" step="1" value="0"
                                            class="form-control form-control-sm">
                                    </td>
                                    <td>
                                        <select name="items[{{ $item->id }}][condition]" class="form-select form-select-sm">
                                            <option value="">—</option>
                                            @foreach ($conditions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Registrar devolucion</button>
                <a href="{{ route('tienda.recepciones') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
