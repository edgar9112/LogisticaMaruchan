@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="page-header animate-fade-in-up">
            <h3 class="mb-1">Reportes</h3>
            <p class="text-muted mb-0">Consulta y exporta la informacion operativa en PDF o Excel.</p>
        </div>

        <div class="row g-3">
            @php
                $reportCards = [
                    'pedidos' => ['name' => 'Pedidos', 'desc' => 'Folio, tienda, almacen, estado y unidades.', 'icon' => '&#9993;', 'color' => '#eff6ff'],
                    'embarques' => ['name' => 'Embarques', 'desc' => 'Traslados con vehiculo, conductor, salida y llegada.', 'icon' => '&#9654;', 'color' => '#f0fdf4'],
                    'incidencias' => ['name' => 'Incidencias', 'desc' => 'Tipo, descripcion y embarque o pedido asociado.', 'icon' => '&#9888;', 'color' => '#fef3c7'],
                    'movimientos' => ['name' => 'Movimientos', 'desc' => 'Bitacora completa de trazabilidad por fecha.', 'icon' => '&#8982;', 'color' => '#f5f3ff'],
                ];
            @endphp

            @foreach ($reportCards as $tipo => $report)
                <div class="col-12 col-md-6 col-lg-3 stagger-child">
                    <div class="card h-100 clickable-card" onclick="window.location='{{ route('reportes.show', $tipo) }}'">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded d-flex align-items-center justify-content-center"
                                    style="width:48px;height:48px;background:{{ $report['color'] }};font-size:1.3rem;">
                                    {!! $report['icon'] !!}
                                </div>
                                <h5 class="card-title mb-0">{{ $report['name'] }}</h5>
                            </div>
                            <p class="card-text text-muted small mb-3">{{ $report['desc'] }}</p>
                            <a href="{{ route('reportes.show', $tipo) }}" class="btn btn-outline-primary btn-sm">Abrir reporte</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
