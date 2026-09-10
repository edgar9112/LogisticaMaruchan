@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center gap-3 mb-3 animate-fade-in-up">
            <a href="{{ route('devoluciones.index') }}" class="btn btn-outline-secondary btn-sm">&larr;</a>
            <div>
                <h3 class="mb-0">Devolucion <span class="text-muted">#{{ $devolucion->id }}</span></h3>
                <p class="text-muted small mb-0">Pedido <strong>{{ $devolucion->order->order_number }}</strong> &mdash; {{ $devolucion->order->store->name }}</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-md-4 stagger-child">
                <div class="card h-100">
                    <div class="card-header"><strong>Estado</strong></div>
                    <div class="card-body">
                        @include('partials.return_status', ['status' => $devolucion->status])
                        <p class="small text-muted mt-2 mb-0">Solicitada el
                            {{ $devolucion->requested_at->format('d/m/Y H:i') }}
                            por {{ $devolucion->requester->name ?? '—' }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 stagger-child">
                <div class="card h-100">
                    <div class="card-header"><strong>Motivo</strong></div>
                    <div class="card-body">
                        <p class="mb-1 fw-bold">{{ $devolucion->reason_label }}</p>
                        @if ($devolucion->note)
                            <p class="mb-0 small text-muted">{{ $devolucion->note }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4 stagger-child">
                <div class="card h-100">
                    <div class="card-header"><strong>Recepcion en almacen</strong></div>
                    <div class="card-body">
                        @if ($devolucion->status === \App\Models\OrderReturn::STATUS_RECIBIDA)
                            <p class="mb-1"><span class="badge bg-success">Recibida</span></p>
                            <p class="small text-muted mb-0">Recibida el
                                {{ $devolucion->received_at?->format('d/m/Y H:i') }}
                                por {{ $devolucion->receiver?->name ?? '—' }}</p>
                        @else
                            <p class="small text-muted mb-0">Pendiente de recibir en almacen.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4 animate-fade-in-up" style="animation-delay:0.1s;">
            <div class="card-header"><strong>Productos devueltos</strong></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($devolucion->items as $item)
                            <tr class="stagger-child">
                                <td><strong>{{ $item->orderItem->presentation->name }}</strong>
                                    <span class="text-muted d-block small">{{ $item->orderItem->presentation->sku }}</span>
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td>{{ \App\Models\OrderReturn::CONDITIONS[$item->condition] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total: {{ $devolucion->items->sum('quantity') }} pzas</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if (\Illuminate\Support\Facades\Auth::user()->hasRole('almacen') && $devolucion->status !== \App\Models\OrderReturn::STATUS_RECIBIDA)
            <form method="POST" action="{{ route('devoluciones.recibir', $devolucion) }}">
                @csrf
                <button class="btn btn-success" onclick="return confirm('Confirmar la recepcion de esta devolucion en almacen?')">
                    Marcar como recibida en almacen
                </button>
            </form>
        @endif
    </div>
@endsection
