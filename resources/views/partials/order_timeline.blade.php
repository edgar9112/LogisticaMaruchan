<div class="card mb-3 animate-fade-in-up">
    <div class="card-header" style="background:linear-gradient(135deg,#1e40af,#2563eb);color:#fff;border:none;">
        <strong>&#8982; Donde esta el pedido?</strong>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <p class="mb-2">
                    <strong>Situacion actual:</strong>
                    @include('partials.order_status', ['status' => $order->status])
                </p>
                <p class="mb-1"><strong>Folio:</strong> <code>{{ $order->order_number }}</code></p>
                <p class="mb-1"><strong>Tienda destino:</strong> {{ $order->store->name }} ({{ $order->store->code }})</p>
                <p class="mb-0"><strong>Almacen:</strong> {{ $order->warehouse?->name ?? '—' }}</p>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span style="width:8px;height:8px;border-radius:50%;background:#2563eb;"></span>
                    <strong class="small">Pedido creado:</strong>
                    <span class="text-muted small">{{ $order->ordered_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $order->received_at ? '#16a34a' : '#e5e7eb' }};"></span>
                    <strong class="small">Recibido en almacen:</strong>
                    <span class="text-muted small">{{ $order->received_at?->format('d/m/Y H:i') ?? '—' }}</span>
                </div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $order->prepared_at ? '#16a34a' : '#e5e7eb' }};"></span>
                    <strong class="small">Preparado:</strong>
                    <span class="text-muted small">{{ $order->prepared_at?->format('d/m/Y H:i') ?? '—' }}</span>
                </div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $order->shipped_at ? '#16a34a' : '#e5e7eb' }};"></span>
                    <strong class="small">Enviado:</strong>
                    <span class="text-muted small">{{ $order->shipped_at?->format('d/m/Y H:i') ?? '—' }}</span>
                </div>
                <div class="d-flex align-items-center gap-2 mb-0">
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $order->completed_at ? '#16a34a' : '#e5e7eb' }};"></span>
                    <strong class="small">Completado:</strong>
                    <span class="text-muted small">{{ $order->completed_at?->format('d/m/Y H:i') ?? '—' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3 animate-fade-in-up" style="animation-delay:0.1s;">
    <div class="card-header"><strong>Productos del pedido</strong></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Presentacion</th>
                    <th class="text-center">Pedido</th>
                    <th class="text-center">Recibido</th>
                    <th class="text-center">Preparado</th>
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
                        <td class="text-center">{{ $item->quantity_received ?? '—' }}</td>
                        <td class="text-center">{{ $item->quantity_prepared ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if ($order->incidents->isNotEmpty())
    <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.15s;">
        <div class="card-header" style="background:#fee2e2;color:#991b1b;border:none;"><strong>&#9888; Incidencias</strong></div>
        <ul class="list-group list-group-flush">
            @foreach ($order->incidents as $incident)
                <li class="list-group-item stagger-child">
                    <div class="d-flex align-items-start gap-2">
                        <span class="badge bg-danger mt-1">&#9679;</span>
                        <div>
                            <strong>{{ ucfirst(str_replace('_', ' ', $incident->type)) }}</strong> — {{ $incident->description }}
                            <span class="text-muted d-block small">
                                {{ $incident->occurred_at->format('d/m/Y H:i') }} — {{ $incident->user->name }}
                            </span>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card animate-fade-in-up" style="animation-delay:0.2s;">
    <div class="card-header"><strong>Historial completo</strong></div>
    <div class="card-body">
        @forelse ($order->movements->sortBy('created_at') as $movement)
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
