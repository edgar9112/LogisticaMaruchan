@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="page-header animate-fade-in-up">
            <h3 class="mb-1">Dashboard operativo</h3>
            <p class="text-muted mb-0">Hola, <strong>{{ $user->name }}</strong>. Indicadores en tiempo real.</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-lg-3 stagger-child">
                <div class="card stat-card text-bg-primary h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon">&#9993;</div>
                        <div>
                            <div class="stat-value" data-count-to="{{ $receivedToday }}">{{ $receivedToday }}</div>
                            <div class="stat-label">Recibidos hoy</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3 stagger-child">
                <div class="card stat-card text-bg-warning h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon">&#9881;</div>
                        <div>
                            <div class="stat-value" data-count-to="{{ $pendingOrders }}">{{ $pendingOrders }}</div>
                            <div class="stat-label">Pedidos pendientes</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3 stagger-child">
                <div class="card stat-card text-bg-secondary h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon">&#10003;</div>
                        <div>
                            <div class="stat-value" data-count-to="{{ $preparedOrders }}">{{ $preparedOrders }}</div>
                            <div class="stat-label">Preparados</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3 stagger-child">
                <div class="card stat-card text-bg-info h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon">&#9654;</div>
                        <div>
                            <div class="stat-value" data-count-to="{{ $activeShipments }}">{{ $activeShipments }}</div>
                            <div class="stat-label">Traslados activos</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3 stagger-child">
                <div class="card stat-card text-bg-success h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon">&#9878;</div>
                        <div>
                            <div class="stat-value" data-count-to="{{ $warehouseStock }}">{{ number_format($warehouseStock, 0) }}</div>
                            <div class="stat-label">Unidades en almacen</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3 stagger-child">
                <div class="card stat-card text-bg-dark h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon">&#9992;</div>
                        <div>
                            <div class="stat-value" data-count-to="{{ $deliveredOrders }}">{{ $deliveredOrders }}</div>
                            <div class="stat-label">Entregas realizadas</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3 stagger-child">
                <div class="card stat-card text-bg-danger h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon">&#9888;</div>
                        <div>
                            <div class="stat-value" data-count-to="{{ $incidents }}">{{ $incidents }}</div>
                            <div class="stat-label">Incidencias</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3 stagger-child">
                <div class="card stat-card h-100" style="background:linear-gradient(135deg,#f1f5f9,#e2e8f0);">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:rgba(0,0,0,0.08);">&#8982;</div>
                        <div>
                            <div class="stat-value">{{ $avgWarehouseDays ?? '—' }}</div>
                            <div class="stat-label">Dias promedio en almacen</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-7 animate-fade-in-up" style="animation-delay:0.2s;">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <strong>Tiempos por etapa (dias promedio)</strong>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0 align-middle">
                            <tbody>
                                @foreach ($timeByStage as $stage => $days)
                                    <tr class="stagger-child">
                                        <td class="ps-3">{{ $stage }}</td>
                                        <td class="text-end pe-3 fw-bold">
                                            @if ($days !== null)
                                               <span class="badge" style="background:{{ $days > 3 ? '#fee2e2;color:#991b1b' : ($days > 1 ? '#fff3cd;color:#664d03' : '#d9edf7;color:#2c7be5') }}">{{ $days }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p class="text-muted small px-3 pb-2 mb-0 mt-2">Dias promedio calculados sobre los pedidos que completaron cada etapa.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 animate-fade-in-up" style="animation-delay:0.3s;">
                <div class="card h-100">
                    <div class="card-header"><strong>Rotacion de inventario</strong></div>
                    <div class="card-body">
                        <div class="row text-center g-2">
                            <div class="col-6">
                                <div class="border rounded p-3 animate-scale-in" style="animation-delay:0.4s;">
                                    <h4 class="mb-0" data-count-to="{{ $stockRotation['stock'] }}">{{ number_format($stockRotation['stock'], 0) }}</h4>
                                    <p class="text-muted small mb-0">Stock actual (pzas)</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 animate-scale-in" style="animation-delay:0.5s;">
                                    <h4 class="mb-0" data-count-to="{{ $stockRotation['out_last_30'] }}">{{ number_format($stockRotation['out_last_30'], 0) }}</h4>
                                    <p class="text-muted small mb-0">Salidas 30 dias</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 animate-scale-in" style="animation-delay:0.6s;">
                                    <h4 class="mb-0">{{ number_format($stockRotation['rotation'], 2) }}</h4>
                                    <p class="text-muted small mb-0">Rotacion (salidas / stock)</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3 animate-scale-in" style="animation-delay:0.7s;">
                                    <h4 class="mb-0">{{ $stockRotation['coverage_days'] !== null ? number_format($stockRotation['coverage_days'], 1) : '—' }}</h4>
                                    <p class="text-muted small mb-0">Cobertura (dias)</p>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small mt-3 mb-0">La rotacion compara las unidades preparadas en los ultimos 30 dias contra el stock actual.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-7 animate-fade-in-up" style="animation-delay:0.3s;">
                <div class="card h-100">
                    <div class="card-header"><strong>Pedidos por tienda</strong></div>
                    <div class="card-body">
                        @forelse ($ordersByStore as $item)
                            <div class="mb-3 stagger-child">
                                <div class="d-flex justify-content-between small">
                                    <span class="fw-medium">{{ $item['store_name'] }}</span>
                                    <strong>{{ $item['total'] }}</strong>
                                </div>
                                <div class="progress" style="height:8px">
                                    <div class="progress-bar" role="progressbar"
                                        style="width: {{ ($item['total'] / max($ordersByStore[0]['total'], 1)) * 100 }}%"
                                        aria-valuenow="{{ $item['total'] }}" aria-valuemin="0" aria-valuemax="{{ $ordersByStore[0]['total'] }}">
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <div class="empty-state-icon">&#9993;</div>
                                <p class="mb-0">Sin pedidos registrados.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-lg-5 animate-fade-in-up" style="animation-delay:0.4s;">
                <div class="card h-100">
                    <div class="card-header"><strong>Resumen rapido</strong></div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3 p-2 rounded" style="background:#f0fdf4;">
                            <span style="font-size:1.5rem;">&#9962;</span>
                            <div>
                                <div class="fw-bold">{{ $totalStores }}</div>
                                <div class="text-muted small">Tiendas activas</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3 mb-3 p-2 rounded" style="background:#eff6ff;">
                            <span style="font-size:1.5rem;">&#9733;</span>
                            <div>
                                <div class="fw-bold">{{ $totalPresentations }}</div>
                                <div class="text-muted small">Presentaciones en catalogo</div>
                            </div>
                        </div>
                        <p class="text-muted small mb-0">Acceso rapido desde el menu lateral.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-lg-6 animate-fade-in-up" style="animation-delay:0.4s;">
                <div class="card h-100">
                    <div class="card-header"><strong>Pedidos por estado</strong></div>
                    <div class="card-body">
                        <div style="height:300px" id="ordersByStatusChart"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 animate-fade-in-up" style="animation-delay:0.5s;">
                <div class="card h-100">
                    <div class="card-header"><strong>Embarques por estado</strong></div>
                    <div class="card-body">
                        <div style="height:300px" id="shipmentsByStatusChart"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-1 mb-4">
            <div class="col-lg-6 animate-fade-in-up" style="animation-delay:0.5s;">
                <div class="card h-100">
                    <div class="card-header"><strong>Pedidos por dia (ultimos 14 dias)</strong></div>
                    <div class="card-body">
                        <div style="height:300px" id="ordersLast14DaysChart"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 animate-fade-in-up" style="animation-delay:0.6s;">
                <div class="card h-100">
                    <div class="card-header"><strong>Incidencias por tipo</strong></div>
                    <div class="card-body">
                        <div style="height:300px" id="incidentsByTypeChart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script data-dashboard>
        window.dashboardData = @json($chartData);
    </script>
    @vite(['resources/js/dashboard.js'])
@endsection
