<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Presentation;
use App\Models\Shipment;
use App\Models\Store;
use App\Support\OrderItemStock;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $data = [
            'user' => Auth::user(),
            'receivedToday' => $this->receivedToday(),
            'pendingOrders' => $this->pendingOrders(),
            'preparedOrders' => $this->preparedOrders(),
            'activeShipments' => $this->activeShipments(),
            'warehouseStock' => $this->warehouseStock(),
            'deliveredOrders' => $this->deliveredOrders(),
            'incidents' => Incident::count(),
            'avgWarehouseDays' => $this->avgWarehouseDays(),
            'ordersByStore' => $this->ordersByStore(),
            'ordersByStatus' => $this->ordersByStatus(),
            'shipmentsByStatus' => $this->shipmentsByStatus(),
            'incidentsByType' => $this->incidentsByType(),
            'ordersLast14Days' => $this->ordersLast14Days(),
            'chartData' => [
                'ordersByStatus' => $this->ordersByStatus(),
                'shipmentsByStatus' => $this->shipmentsByStatus(),
                'incidentsByType' => $this->incidentsByType(),
                'ordersLast14Days' => $this->ordersLast14Days(),
            ],
            'totalStores' => Store::count(),
            'totalPresentations' => Presentation::count(),
            'timeByStage' => $this->timeByStage(),
            'stockRotation' => $this->stockRotation(),
        ];

        return view('dashboard', $data);
    }

    private function receivedToday(): int
    {
        return Order::whereDate('received_at', today())->count();
    }

    private function pendingOrders(): int
    {
        return Order::whereNotIn('status', [
            Order::STATUS_RECIBIDO_TIENDA,
            Order::STATUS_CERRADO,
        ])->count();
    }

    private function preparedOrders(): int
    {
        return Order::whereIn('status', [
            Order::STATUS_PREPARADO,
            Order::STATUS_ASIGNADO_EMBARQUE,
        ])->count();
    }

    private function activeShipments(): int
    {
        return Shipment::whereNotIn('status', [
            Shipment::STATUS_ENTREGADO,
            Shipment::STATUS_CERRADO,
        ])->count();
    }

    private function warehouseStock(): int
    {
        return OrderItemStock::sumInWarehouse();
    }

    private function deliveredOrders(): int
    {
        return Order::whereIn('status', [
            Order::STATUS_RECIBIDO_TIENDA,
            Order::STATUS_CERRADO,
        ])->count();
    }

    private function avgWarehouseDays(): ?float
    {
        $seconds = Order::whereNotNull('shipped_at')
            ->whereNotNull('received_at')
            ->get()
            ->map(fn ($order) => $order->shipped_at->diffInSeconds($order->received_at))
            ->avg();

        return $seconds !== null ? round($seconds / 86400, 2) : null;
    }

    private function ordersByStore(): array
    {
        return Order::query()
            ->selectRaw('stores.name as store_name, count(*) as total')
            ->join('stores', 'stores.id', '=', 'orders.store_id')
            ->groupBy('stores.id', 'stores.name')
            ->orderByDesc('total')
            ->take(6)
            ->get()
            ->toArray();
    }

    private function ordersByStatus(): array
    {
        $counts = Order::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $labels = [
            Order::STATUS_CREADO,
            Order::STATUS_CONFIRMADO,
            Order::STATUS_EN_ALMACEN,
            Order::STATUS_RECIBIDO,
            Order::STATUS_CLASIFICADO,
            Order::STATUS_PREPARADO,
            Order::STATUS_ASIGNADO_EMBARQUE,
            Order::STATUS_CARGADO,
            Order::STATUS_EN_TRANSITO,
            Order::STATUS_RECIBIDO_TIENDA,
            Order::STATUS_CERRADO,
        ];

        return array_map(fn ($status) => [
            'label' => str_replace('_', ' ', $status),
            'total' => $counts[$status] ?? 0,
        ], $labels);
    }

    private function shipmentsByStatus(): array
    {
        $counts = Shipment::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return array_map(fn ($status) => [
            'label' => str_replace('_', ' ', $status),
            'total' => $counts[$status] ?? 0,
        ], Shipment::STATUSES);
    }

    private function incidentsByType(): array
    {
        $counts = Incident::query()
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $types = array_unique([
            ...array_keys(Incident::ROUTE_INCIDENT_TYPES),
            Incident::TYPE_FALTANTE,
            Incident::TYPE_SOBRANTE,
            Incident::TYPE_DANADO,
            Incident::TYPE_MERCANCIA_NO_LOCALIZADA,
            Incident::TYPE_DIFERENCIA,
            Incident::TYPE_ENTREGA_RECHAZADA,
        ]);

        return array_map(fn ($type) => [
            'label' => Incident::ROUTE_INCIDENT_TYPES[$type] ?? ucfirst(str_replace('_', ' ', $type)),
            'total' => $counts[$type] ?? 0,
        ], $types);
    }

    private function ordersLast14Days(): array
    {
        $byDay = Order::where('ordered_at', '>=', today()->subDays(13))
            ->get()
            ->groupBy(fn ($order) => $order->ordered_at->toDateString());

        $days = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $days[] = [
                'date' => $date->format('d/m'),
                'total' => $byDay->get($date->toDateString(), collect())->count(),
            ];
        }

        return $days;
    }

    /**
     * Tiempo promedio (en días) entre dos fechas de todos los pedidos que ya las tienen.
     */
    private function averageDiffDays(string $from, string $to): ?float
    {
        $seconds = Order::whereNotNull($from)
            ->whereNotNull($to)
            ->get()
            ->map(fn (Order $order) => $order->{$from}->diffInSeconds($order->{$to}))
            ->avg();

        return $seconds === null ? null : round($seconds / 86400, 2);
    }

    private function timeByStage(): array
    {
        return [
            'Recepción (pedido → entrada)' => $this->averageDiffDays('ordered_at', 'received_at'),
            'Preparación (entrada → preparado)' => $this->averageDiffDays('received_at', 'prepared_at'),
            'En almacén (recepción → envío)' => $this->averageDiffDays('received_at', 'shipped_at'),
            'Tránsito y entrega (envío → cierre)' => $this->averageDiffDays('shipped_at', 'completed_at'),
            'Ciclo total (pedido → cerrado)' => $this->averageDiffDays('ordered_at', 'completed_at'),
        ];
    }

    private function stockRotation(): array
    {
        $stock = OrderItemStock::sumInWarehouse();

        $outLast30 = (int) OrderItem::query()
            ->whereHas('order', fn ($query) => $query->where('prepared_at', '>=', now()->subDays(30)))
            ->sum('quantity_prepared');

        $rotation = $stock > 0 ? round($outLast30 / $stock, 2) : 0.0;
        $coverage = $outLast30 > 0 ? round($stock / ($outLast30 / 30), 1) : null;

        return [
            'stock' => $stock,
            'out_last_30' => $outLast30,
            'rotation' => $rotation,
            'coverage_days' => $coverage,
        ];
    }
}