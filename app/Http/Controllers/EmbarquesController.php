<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\Store;
use App\Models\Vehicle;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class EmbarquesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:logistica,admin']);
    }

    public function index()
    {
        $shipments = Shipment::with(['originWarehouse', 'destinationStore', 'vehicle', 'orders'])
            ->orderByDesc('created_at')
            ->get();

        return view('embarques.index', compact('shipments'));
    }

    public function create()
    {
        $stores = Store::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $vehicles = Vehicle::where('active', true)->orderBy('code')->get();

        $orders = Order::with(['store', 'items'])
            ->whereIn('status', [Order::STATUS_PREPARADO])
            ->orderBy('prepared_at')
            ->get()
            ->groupBy('store_id');

        return view('embarques.create', compact('stores', 'warehouses', 'vehicles', 'orders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'origin_warehouse_id' => ['required', 'exists:warehouses,id'],
            'destination_store_id' => ['required', 'exists:stores,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'scheduled_at' => ['nullable', 'date'],
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['exists:orders,id'],
        ]);

        $orders = Order::whereIn('id', $validated['order_ids'])->get();

        foreach ($orders as $order) {
            if ($order->status !== Order::STATUS_PREPARADO) {
                return back()
                    ->withErrors("El pedido {$order->order_number} no está preparado (PREPARADO).")
                    ->withInput();
            }
            if ((int) $order->store_id !== (int) $validated['destination_store_id']) {
                return back()
                    ->withErrors("El pedido {$order->order_number} pertenece a otra tienda.")
                    ->withInput();
            }
        }

        $shipment = Shipment::create([
            'shipment_number' => Shipment::generateShipmentNumber(),
            'origin_warehouse_id' => $validated['origin_warehouse_id'],
            'destination_store_id' => $validated['destination_store_id'],
            'vehicle_id' => $validated['vehicle_id'],
            'driver_name' => $validated['driver_name'] ?? null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
        ]);

        $shipment->orders()->attach($validated['order_ids']);

        foreach ($orders as $order) {
            $order->update(['status' => Order::STATUS_ASIGNADO_EMBARQUE]);
            $order->recordMovement(
                Order::STATUS_ASIGNADO_EMBARQUE,
                'Asignado al embarque '.$shipment->shipment_number,
                metadata: ['shipment_number' => $shipment->shipment_number],
            );
        }

        $shipment->recordMovement(
            Shipment::STATUS_PREPARADO,
            'Embarque creado y pedidos asignados',
            metadata: ['orders' => $orders->count(), 'vehicle' => $shipment->vehicle_id],
        );

        return redirect()
            ->route('embarques.show', $shipment)
            ->with('success', "Embarque {$shipment->shipment_number} creado con {$orders->count()} pedidos.");
    }

    public function show(Shipment $shipment)
    {
        $shipment->load([
            'originWarehouse',
            'destinationStore',
            'vehicle',
            'orders.items.presentation',
            'orders.store',
            'movements.user',
        ]);

        return view('embarques.show', compact('shipment'));
    }

    public function cargar(Shipment $shipment)
    {
        if ($shipment->status !== Shipment::STATUS_PREPARADO) {
            return back()->withErrors('Solo se puede cargar un embarque en estado PREPARADO.');
        }

        $vehicle = $shipment->vehicle;

        $shipment->update(['status' => Shipment::STATUS_CARGADO]);

        foreach ($shipment->orders as $order) {
            $order->update(['status' => Order::STATUS_CARGADO]);
            $order->recordMovement(
                Order::STATUS_CARGADO,
                'Mercancía cargada en vehículo '.( $vehicle?->plate ?? '' ),
                metadata: ['vehicle' => $vehicle?->code, 'shipment' => $shipment->shipment_number],
            );
        }

        $shipment->recordMovement(
            Shipment::STATUS_CARGADO,
            'Embarque cargado en el vehículo',
            metadata: ['vehicle' => $vehicle?->code, 'plate' => $vehicle?->plate],
        );

        return redirect()
            ->route('embarques.show', $shipment)
            ->with('success', "Embarque {$shipment->shipment_number} cargado en el vehículo.");
    }
}