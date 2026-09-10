<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #222; }
        h1 { color: #0d6efd; font-size: 16pt; margin: 0 0 2pt 0; }
        .meta { color: #666; font-size: 8.5pt; margin-bottom: 4pt; }
        .filters { color: #555; font-size: 8.5pt; margin-bottom: 8pt; }
        table { width: 100%; border-collapse: collapse; font-size: 8.5pt; margin-top: 6pt; }
        th { background: #0d6efd; color: #fff; padding: 5pt 6pt; text-align: left; border: 1px solid #0a58ca; }
        td { padding: 5pt 6pt; border: 1px solid #c9d4e3; vertical-align: top; }
        tr:nth-child(even) td { background: #f2f6fb; }
        .empty { color: #555; margin-top: 12pt; }
        .footer { text-align: center; color: #888; font-size: 8pt; margin-top: 16pt; }
        .pagebreak { page-break-inside: auto; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">Generado el {{ now()->format('d/m/Y H:i') }}</div>

    @php
        $filtros = [];
        if ($filters['desde'] ?? null) {
            $filtros[] = 'Desde: '.$filters['desde'];
        }
        if ($filters['hasta'] ?? null) {
            $filtros[] = 'Hasta: '.$filters['hasta'];
        }
        if (! empty($filters[$statusFilterName] ?? null)) {
            $filtros[] = 'Estado: '.$filters[$statusFilterName];
        }
    @endphp

    @if($filtros)
        <div class="filters">Filtros aplicados: {{ implode(' · ', $filtros) }}</div>
    @endif

    @if(count($rows) > 0)
        <table>
            <thead>
                <tr>
                    @foreach($columns as $col)
                        <th>{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        @foreach($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="empty">No hay registros para los filtros seleccionados.</p>
    @endif

    <div class="footer">Logística Maruchan · {{ $title }} · {{ count($rows) }} registros</div>
</body>
</html>