@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center gap-3 mb-3 animate-fade-in-up">
            <a href="{{ route('reportes.index') }}" class="btn btn-outline-secondary btn-sm">&larr;</a>
            <div>
                <h3 class="mb-0">{{ $title }}</h3>
                <p class="text-muted small mb-0">{{ $subtitle }}</p>
            </div>
        </div>

        <div class="card mb-3 animate-fade-in-up" style="animation-delay:0.1s;">
            <div class="card-body">
                <form method="GET" action="{{ route('reportes.show', $tipo) }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-auto">
                            <label class="form-label mb-0 small fw-medium">Desde</label>
                            <input type="date" name="desde" value="{{ $filters['desde'] }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-auto">
                            <label class="form-label mb-0 small fw-medium">Hasta</label>
                            <input type="date" name="hasta" value="{{ $filters['hasta'] }}" class="form-control form-control-sm">
                        </div>
                        @if($statuses)
                            <div class="col-auto">
                                <label class="form-label mb-0 small fw-medium">Estado</label>
                                <select name="{{ $statusFilterName }}" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}" @selected(($filters[$statusFilterName] ?? null) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('reportes.show', $tipo) }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                        </div>
                    </div>
                </form>
                <hr>
                <div class="d-flex gap-2">
                    <a href="{{ route('reportes.pdf', array_merge(['reporte' => $tipo], request()->query())) }}"
                       class="btn btn-danger btn-sm">Descargar PDF</a>
                    <a href="{{ route('reportes.excel', array_merge(['reporte' => $tipo], request()->query())) }}"
                       class="btn btn-success btn-sm">Descargar Excel (.xlsx)</a>
                </div>
            </div>
        </div>

        <p class="text-muted small animate-fade-in-up" style="animation-delay:0.15s;">{{ count($rows) }} registro(s) mostrados.</p>

        <div class="card animate-fade-in-up" style="animation-delay:0.2s;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            @foreach($columns as $col)
                                <th>{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr class="stagger-child">
                                @foreach($row as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) }}">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">&#9783;</div>
                                        <p class="mb-0">No hay registros para los filtros seleccionados.</p>
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
