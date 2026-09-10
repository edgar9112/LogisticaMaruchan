@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="page-header animate-fade-in-up">
            <h3 class="mb-1">Preparar pedido</h3>
            <p class="text-muted mb-0">Confirma las cantidades que se empacan/contabilizan para el embarque.</p>
        </div>

        <div class="card mb-4 animate-fade-in-up" style="animation-delay:0.1s;">
            <div class="card-header" style="background:linear-gradient(135deg,#0891b2,#06b6d4);color:#fff;border:none;">
                <strong>{{ $order->order_number }}</strong> &mdash; {{ $order->store->name }}
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Estado:</strong> @include('partials.order_status', ['status' => $order->status])</p>
                <p class="mb-1"><strong>Almacen:</strong> {{ $order->warehouse?->name ?? '—' }}</p>
                <p class="mb-0"><strong>Recibido:</strong> {{ $order->received_at?->format('d/m/Y H:i') ?? '—' }}</p>
            </div>
        </div>

        <div class="card animate-fade-in-up" style="animation-delay:0.15s;">
            <div class="card-header"><strong>Productos a preparar</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('almacen.pedidos.preparar.guardar', $order) }}">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-3">
                            <thead>
                                <tr>
                                    <th>Presentacion</th>
                                    <th class="text-center">Pedidas</th>
                                    <th class="text-center">Recibidas</th>
                                    <th class="text-center">A preparar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr class="stagger-child">
                                        <td>{{ ucfirst($item->presentation->presentation_type) }} &middot; {{ $item->presentation->flavor }}
                                            <span class="text-muted d-block small">{{ $item->presentation->sku }}</span>
                                        </td>
                                        <td class="text-center fw-bold">{{ $item->quantity_requested }}</td>
                                        <td class="text-center">{{ $item->quantity_received ?? '—' }}</td>
                                        <td class="text-center" style="width:140px">
                                            <input type="number" name="quantities[{{ $item->id }}]" min="0"
                                                class="form-control form-control-sm text-center"
                                                value="{{ old('quantities.' . $item->id, $item->quantity_received ?? $item->quantity_requested) }}"
                                                style="width:110px; display:inline-block">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('almacen.pendientes') }}" class="btn btn-outline-secondary">Cancelar</a>
                        <button class="btn btn-info">Confirmar preparacion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
