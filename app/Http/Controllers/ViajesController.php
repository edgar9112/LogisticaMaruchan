<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\IncidentAlert;
use App\Mail\OrderDeliveredAlert;
use App\Mail\ShipmentDepartedAlert;
use App\Models\Incident;
use App\Models\Order;
use App\Models\Shipment;
use App\Support\EmailNotifier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ViajesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:transporte,admin']);
    }

    public function index()
    {
        $active = Shipment::with(['originWarehouse', 'destinationStore', 'vehicle', 'orders'])
            ->whereIn('status', [
                Shipment::STATUS_PREPARADO,
                Shipment::STATUS_CARGADO,
                Shipment::STATUS_EN_TRANSITO,
            ])
            ->orderByDesc('status')
            ->orderBy('created_at')
            ->get();

        $history = Shipment::with(['destinationStore', 'vehicle'])
            ->whereIn('status', [
                Shipment::STATUS_ENTREGADO,
                Shipment::STATUS_CERRADO,
            ])
            ->orderByDesc('arrived_at')
            ->take(10)
            ->get();

        return view('viajes.index', compact('active', 'history'));
    }

    public function show(Shipment $shipment)
    {
        $shipment->load([
            'originWarehouse',
            'destinationStore',
            'vehicle',
            'orders.items.presentation',
            'movements.user',
            'incidents.user',
        ]);

        return view('viajes.show', compact('shipment'));
    }

    public function salir(Shipment $shipment)
    {
        if ($shipment->status !== Shipment::STATUS_CARGADO) {
            return back()->withErrors('Solo se puede iniciar el viaje de un embarque CARGADO.');
        }

        $shipment->update([
            'status' => Shipment::STATUS_EN_TRANSITO,
            'departed_at' => now(),
        ]);

        foreach ($shipment->orders as $order) {
            $order->update(['status' => Order::STATUS_EN_TRANSITO, 'shipped_at' => now()]);
            $order->recordMovement(
                Order::STATUS_EN_TRANSITO,
                'Mercancía despachada hacia la tienda',
                metadata: ['shipment' => $shipment->shipment_number],
            );
        }

        $shipment->recordMovement(
            Shipment::STATUS_EN_TRANSITO,
            'Vehículo en ruta hacia la tienda',
            metadata: ['destination' => $shipment->destinationStore->name],
        );

        $shipment->load(['destinationStore', 'vehicle', 'orders:id,order_number']);
        EmailNotifier::toAdmins(new ShipmentDepartedAlert($shipment));

        return redirect()
            ->route('viajes.show', $shipment)
            ->with('success', "El embarque {$shipment->shipment_number} está en tránsito.");
    }

    public function registrarIncidencia(Request $request, Shipment $shipment)
    {
        if ($shipment->status !== Shipment::STATUS_EN_TRANSITO) {
            return back()->withErrors('Solo se pueden registrar incidencias en ruta para un embarque EN_TRANSITO.');
        }

        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(Incident::ROUTE_INCIDENT_TYPES))],
            'description' => ['required', 'string', 'max:1000'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $incident = $shipment->incidents()->create([
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'description' => $validated['description'],
            'occurred_at' => $validated['occurred_at'] ?? now(),
        ]);

        $shipment->recordMovement(
            Shipment::STATUS_EN_TRANSITO,
            'Incidencia en ruta: '.$incident->type_label,
            $incident->description,
            metadata: ['incident_id' => $incident->id],
        );

        EmailNotifier::toAdmins(new IncidentAlert($incident->load('shipment')));

        return redirect()
            ->route('viajes.show', $shipment)
            ->with('success', 'Incidencia en ruta registrada correctamente.');
    }

    public function llegar(Shipment $shipment)
    {
        if ($shipment->status !== Shipment::STATUS_EN_TRANSITO) {
            return back()->withErrors('Solo se puede registrar la llegada de un embarque EN_TRANSITO.');
        }

        $shipment->update([
            'status' => Shipment::STATUS_ENTREGADO,
            'arrived_at' => now(),
        ]);

        foreach ($shipment->orders as $order) {
            $order->update(['status' => Order::STATUS_RECIBIDO_TIENDA]);
            $order->recordMovement(
                Order::STATUS_RECIBIDO_TIENDA,
                'Mercancía entregada y recibida en tienda',
                metadata: ['shipment' => $shipment->shipment_number],
            );
        }

        $shipment->recordMovement(
            Shipment::STATUS_ENTREGADO,
            'Embarque entregado en tienda',
            metadata: ['destination' => $shipment->destinationStore->name],
        );

        foreach ($shipment->orders as $order) {
            EmailNotifier::toAdmins(new OrderDeliveredAlert($order->load(['store'])));
        }

        return redirect()
            ->route('viajes.show', $shipment)
            ->with('success', "El embarque {$shipment->shipment_number} fue entregado en tienda.");
    }
}