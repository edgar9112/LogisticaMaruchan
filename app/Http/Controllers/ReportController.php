<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Movement;
use App\Models\Order;
use App\Models\Shipment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    private const TIPOS = ['pedidos', 'embarques', 'incidencias', 'movimientos'];

    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        return view('reportes.index');
    }

    public function show(string $reporte)
    {
        return view('reportes.show', $this->buildReport($reporte));
    }

    public function pdf(string $reporte)
    {
        $data = $this->buildReport($reporte);

        $pdf = Pdf::loadView('reportes.pdf', $data)->setPaper('a4', count($data['columns']) > 6 ? 'landscape' : 'portrait');

        return $pdf->download('reporte-'.$data['tipo'].'-'.now()->format('Ymd-His').'.pdf');
    }

    public function excel(string $reporte)
    {
        $data = $this->buildReport($reporte);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(ucfirst($data['tipo']));

        $lastColumn = $this->columnLetter(count($data['columns']));

        $sheet->fromArray([$data['columns']], null, 'A1');
        $sheet->getStyle('A1:'.$lastColumn.'1')
            ->getFont()
            ->setBold(true)
            ->setColor(new Color(Color::COLOR_WHITE));
        $sheet->getStyle('A1:'.$lastColumn.'1')
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('0D6EFD');

        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $rowIndex = 2;
        foreach ($data['rows'] as $row) {
            $sheet->fromArray(array_values($row), null, 'A'.$rowIndex);
            $rowIndex++;
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'reporte-'.$data['tipo'].'-'.now()->format('Ymd-His').'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function buildReport(string $reporte): array
    {
        if (! in_array($reporte, self::TIPOS, true)) {
            abort(404);
        }

        $desde = request('desde') ?: null;
        $hasta = request('hasta') ?: null;

        return match ($reporte) {
            'pedidos' => $this->reportePedidos($desde, $hasta, request('estado') ?: null),
            'embarques' => $this->reporteEmbarques($desde, $hasta, request('estado') ?: null),
            'incidencias' => $this->reporteIncidencias($desde, $hasta, request('tipo') ?: null),
            'movimientos' => $this->reporteMovimientos($desde, $hasta),
        };
    }

    private function reportePedidos(?string $desde, ?string $hasta, ?string $estado): array
    {
        $query = Order::query()
            ->with(['store', 'warehouse'])
            ->withSum('items as total_units', 'quantity_requested');

        $this->applyDateRange($query, 'ordered_at', $desde, $hasta);

        if ($estado) {
            $query->where('status', $estado);
        }

        $rows = $query->orderByDesc('ordered_at')->limit(500)->get()->map(fn (Order $order) => [
            $order->order_number,
            $order->store?->name ?? '—',
            $order->warehouse?->name ?? '—',
            $order->status,
            $order->ordered_at?->format('d/m/Y H:i') ?? '—',
            (string) ($order->total_units ?? '0'),
        ]);

        return [
            'tipo' => 'pedidos',
            'title' => 'Reporte de pedidos',
            'subtitle' => 'Folio, tienda destino, almacén origen, estado, fecha de creación y unidades.',
            'columns' => ['Folio', 'Tienda', 'Almacén', 'Estado', 'Creado', 'Unidades'],
            'rows' => $rows->values()->all(),
            'filters' => $this->filters($desde, $hasta, $estado),
            'statuses' => array_combine(
                Order::STATUSES,
                array_map(fn ($status) => str_replace('_', ' ', $status), Order::STATUSES)
            ),
            'statusFilterName' => 'estado',
        ];
    }

    private function reporteEmbarques(?string $desde, ?string $hasta, ?string $estado): array
    {
        $query = Shipment::query()
            ->with(['originWarehouse', 'destinationStore', 'vehicle'])
            ->withCount('orders');

        $this->applyDateRange($query, 'created_at', $desde, $hasta);

        if ($estado) {
            $query->where('status', $estado);
        }

        $rows = $query->orderByDesc('created_at')->limit(500)->get()->map(fn (Shipment $shipment) => [
            $shipment->shipment_number,
            $shipment->originWarehouse?->name ?? '—',
            $shipment->destinationStore?->name ?? '—',
            $shipment->vehicle?->plate ?? '—',
            $shipment->driver_name ?? '—',
            $shipment->status,
            $shipment->scheduled_at?->format('d/m/Y') ?? '—',
            $shipment->departed_at?->format('d/m/Y H:i') ?? '—',
            $shipment->arrived_at?->format('d/m/Y H:i') ?? '—',
        ]);

        return [
            'tipo' => 'embarques',
            'title' => 'Reporte de embarques',
            'subtitle' => 'Traslados con origen, destino, vehículo, conductor, estado y fechas de salida/llegada.',
            'columns' => ['Folio', 'Origen', 'Destino', 'Placa', 'Conductor', 'Estado', 'Programado', 'Salida', 'Llegada'],
            'rows' => $rows->values()->all(),
            'filters' => $this->filters($desde, $hasta, $estado),
            'statuses' => array_combine(
                Shipment::STATUSES,
                array_map(fn ($status) => str_replace('_', ' ', $status), Shipment::STATUSES)
            ),
            'statusFilterName' => 'estado',
        ];
    }

    private function reporteIncidencias(?string $desde, ?string $hasta, ?string $tipo): array
    {
        $query = Incident::query()
            ->with(['shipment', 'order', 'user']);

        $this->applyDateRange($query, 'created_at', $desde, $hasta);

        if ($tipo) {
            $query->where('type', $tipo);
        }

        $rows = $query->orderByDesc('created_at')->limit(500)->get()->map(fn (Incident $incident) => [
            $incident->type_label,
            $incident->description,
            $incident->shipment?->shipment_number ?? '—',
            $incident->order?->order_number ?? '—',
            $incident->user?->name ?? '—',
            $incident->created_at->format('d/m/Y H:i'),
        ]);

        return [
            'tipo' => 'incidencias',
            'title' => 'Reporte de incidencias',
            'subtitle' => 'Tipos de incidencia, descripción, embarque o pedido asociado y usuario que la registró.',
            'columns' => ['Tipo', 'Descripción', 'Embarque', 'Pedido', 'Usuario', 'Fecha'],
            'rows' => $rows->values()->all(),
            'filters' => $this->filters($desde, $hasta, null, $tipo),
            'statuses' => $this->incidentTypes(),
            'statusFilterName' => 'tipo',
        ];
    }

    private function reporteMovimientos(?string $desde, ?string $hasta): array
    {
        $query = Movement::query()
            ->with(['user', 'trackable']);

        $this->applyDateRange($query, 'created_at', $desde, $hasta);

        $rows = $query->orderByDesc('created_at')->limit(500)->get()->map(fn (Movement $movement) => [
            $this->trackableLabel($movement),
            $movement->trackable?->order_number ?? $movement->trackable?->shipment_number ?? '—',
            $movement->state,
            $movement->action,
            $movement->description ?? '—',
            $movement->user?->name ?? '—',
            $movement->created_at->format('d/m/Y H:i'),
        ]);

        return [
            'tipo' => 'movimientos',
            'title' => 'Reporte de movimientos',
            'subtitle' => 'Bitácora de trazabilidad con el tipo de registro, folio, estado y acción de cada movimiento.',
            'columns' => ['Registro', 'Folio', 'Estado', 'Acción', 'Descripción', 'Usuario', 'Fecha'],
            'rows' => $rows->values()->all(),
            'filters' => $this->filters($desde, $hasta, null, null),
            'statuses' => [],
            'statusFilterName' => 'estado',
        ];
    }

    private function filters(?string $desde, ?string $hasta, ?string $estado, ?string $tipo = null): array
    {
        return [
            'desde' => $desde,
            'hasta' => $hasta,
            'estado' => $estado,
            'tipo' => $tipo,
        ];
    }

    private function applyDateRange(Builder $query, string $column, ?string $desde, ?string $hasta): void
    {
        if ($desde) {
            $query->whereDate($column, '>=', $desde);
        }

        if ($hasta) {
            $query->whereDate($column, '<=', $hasta);
        }
    }

    private function incidentTypes(): array
    {
        $types = array_unique([
            ...array_keys(Incident::ROUTE_INCIDENT_TYPES),
            Incident::TYPE_FALTANTE,
            Incident::TYPE_SOBRANTE,
            Incident::TYPE_DANADO,
            Incident::TYPE_MERCANCIA_NO_LOCALIZADA,
            Incident::TYPE_DIFERENCIA,
            Incident::TYPE_ENTREGA_RECHAZADA,
        ]);

        return array_combine(
            $types,
            array_map(
                fn ($type) => Incident::ROUTE_INCIDENT_TYPES[$type] ?? ucfirst(str_replace('_', ' ', $type)),
                $types
            )
        );
    }

    private function trackableLabel(Movement $movement): string
    {
        $type = $movement->trackable_type ?? '';

        return match (true) {
            str_contains($type, 'Order') => 'Pedido',
            str_contains($type, 'Shipment') => 'Embarque',
            default => '—',
        };
    }

    private function columnLetter(int $count): string
    {
        return $count <= 26 ? chr(64 + $count) : 'A'.chr(64 + $count - 26);
    }
}