<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\OrderReceivedAlert;
use App\Models\Incident;
use App\Models\Order;
use App\Support\EmailNotifier;
use App\Support\Qr;
use Illuminate\Http\Request;

class AlmacenController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:almacen,admin']);
    }

    public function pendientes()
    {
        $pending = Order::with(['store', 'creator'])
            ->whereIn('status', [
                Order::STATUS_CREADO,
                Order::STATUS_CONFIRMADO,
                Order::STATUS_EN_ALMACEN,
            ])
            ->orderBy('ordered_at')
            ->get();

        $recent = Order::with(['store'])
            ->whereIn('status', [
                Order::STATUS_RECIBIDO,
                Order::STATUS_CLASIFICADO,
                Order::STATUS_PREPARADO,
            ])
            ->orderByDesc('received_at')
            ->take(10)
            ->get();

        return view('almacen.pendientes', [
            'pending' => $pending,
            'recent' => $recent,
            'counts' => $this->countsByStatus(),
        ]);
    }

    public function recibir(Order $order)
    {
        if (! in_array($order->status, [Order::STATUS_CREADO, Order::STATUS_CONFIRMADO])) {
            return redirect()
                ->route('almacen.pendientes')
                ->withErrors('Este pedido no puede recibirse en almacén.');
        }

        $order->load(['store', 'items.presentation']);

        return view('almacen.recibir', compact('order'));
    }

    public function guardarEntrada(Request $request, Order $order)
    {
        if (! in_array($order->status, [Order::STATUS_CREADO, Order::STATUS_CONFIRMADO])) {
            return redirect()
                ->route('almacen.pendientes')
                ->withErrors('Este pedido no puede recibirse en almacén.');
        }

        $validated = $request->validate([
            'quantities' => ['required', 'array'],
            'quantities.*' => ['required', 'integer', 'min:0'],
            'incident_type' => ['nullable', 'string', 'max:100'],
            'incident_description' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ($order->items as $item) {
            $quantity = $validated['quantities'][$item->id] ?? $item->quantity_requested;
            $item->update(['quantity_received' => $quantity]);
        }

        $difference = $order->items->sum('quantity_received') - $order->items->sum('quantity_requested');

        $order->update([
            'status' => Order::STATUS_EN_ALMACEN,
            'received_at' => now(),
        ]);

        $order->recordMovement(
            Order::STATUS_EN_ALMACEN,
            'Mercancía recibida en almacén',
            metadata: ['difference' => $difference],
        );

        if ($request->filled('incident_type')) {
            $order->incidents()->create([
                'user_id' => auth()->id(),
                'type' => $request->incident_type,
                'description' => $request->incident_description ?? 'Sin descripción',
                'occurred_at' => now(),
            ]);
        }

        EmailNotifier::toAdmins(new OrderReceivedAlert($order->load(['store', 'warehouse', 'items'])));

        return redirect()
            ->route('almacen.pendientes')
            ->with('success', "Pedido {$order->order_number} recibido en almacén.");
    }

    public function confirmarEntrada(Order $order)
    {
        if ($order->status !== Order::STATUS_EN_ALMACEN) {
            return redirect()
                ->route('almacen.pendientes')
                ->withErrors('Para confirmar, el pedido debe estar en almacén.');
        }

        $order->update(['status' => Order::STATUS_RECIBIDO]);

        $order->recordMovement(
            Order::STATUS_RECIBIDO,
            'Entrada verificada y confirmada en almacén',
        );

        return redirect()
            ->route('almacen.pendientes')
            ->with('success', "Pedido {$order->order_number} verificado correctamente.");
    }

    public function clasificar(Order $order)
    {
        if ($order->status !== Order::STATUS_RECIBIDO) {
            return redirect()
                ->route('almacen.pendientes')
                ->withErrors('Solo se pueden clasificar pedidos verificados (RECIBIDO).');
        }

        $order->load(['store', 'warehouse', 'items.presentation']);

        return view('almacen.clasificar', compact('order'));
    }

    public function guardarClasificacion(Request $request, Order $order)
    {
        if ($order->status !== Order::STATUS_RECIBIDO) {
            return redirect()
                ->route('almacen.pendientes')
                ->withErrors('Solo se pueden clasificar pedidos verificados (RECIBIDO).');
        }

        $validated = $request->validate([
            'location' => ['required', 'string', 'max:200'],
        ]);

        $order->update(['status' => Order::STATUS_CLASIFICADO]);

        $order->recordMovement(
            Order::STATUS_CLASIFICADO,
            'Mercancía clasificada y ubicada en almacén',
            metadata: ['location' => $validated['location']],
        );

        return redirect()
            ->route('almacen.pendientes')
            ->with('success', "Pedido {$order->order_number} clasificado en: {$validated['location']}.");
    }

    public function preparar(Order $order)
    {
        if ($order->status !== Order::STATUS_CLASIFICADO) {
            return redirect()
                ->route('almacen.pendientes')
                ->withErrors('Solo se pueden preparar pedidos clasificados (CLASIFICADO).');
        }

        $order->load(['store', 'warehouse', 'items.presentation']);

        return view('almacen.preparar', compact('order'));
    }

    public function guardarPreparacion(Request $request, Order $order)
    {
        if ($order->status !== Order::STATUS_CLASIFICADO) {
            return redirect()
                ->route('almacen.pendientes')
                ->withErrors('Solo se pueden preparar pedidos clasificados (CLASIFICADO).');
        }

        $validated = $request->validate([
            'quantities' => ['required', 'array'],
            'quantities.*' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($order->items as $item) {
            $quantity = $validated['quantities'][$item->id] ?? $item->quantity_received ?? $item->quantity_requested;
            $item->update(['quantity_prepared' => $quantity]);
        }

        $order->update([
            'status' => Order::STATUS_PREPARADO,
            'prepared_at' => now(),
        ]);

        $order->recordMovement(
            Order::STATUS_PREPARADO,
            'Pedido preparado y listo para embarque',
            metadata: ['units' => $order->items->sum('quantity_prepared')],
        );

        return redirect()
            ->route('almacen.pendientes')
            ->with('success', "Pedido {$order->order_number} preparado para embarque.");
    }

    public function show(Order $order)
    {
        $order->load([
            'store',
            'creator',
            'warehouse',
            'items.presentation',
            'movements.user',
            'incidents.user',
        ]);

        return view('almacen.show', [
            'order' => $order,
            'qr' => Qr::svgDataUri($order->order_number),
        ]);
    }

    private function countsByStatus(): array
    {
        return Order::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }
}