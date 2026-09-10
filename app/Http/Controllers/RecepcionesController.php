<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;

class RecepcionesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:tienda,admin']);
    }

    public function index()
    {
        $query = Order::with(['store', 'items', 'shipments'])
            ->whereIn('status', [Order::STATUS_RECIBIDO_TIENDA, Order::STATUS_CERRADO]);

        if (Auth::user()?->store_id) {
            $query->where('store_id', Auth::user()->store_id);
        }

        $orders = $query->orderByDesc('status')->orderByDesc('updated_at')->get();

        $recentlyClosed = $orders->where('status', Order::STATUS_CERRADO);
        $pendingConfirmation = $orders->where('status', Order::STATUS_RECIBIDO_TIENDA);

        return view('tienda.recepciones', compact('pendingConfirmation', 'recentlyClosed'));
    }

    public function confirmar(Order $order)
    {
        if ($order->status !== Order::STATUS_RECIBIDO_TIENDA) {
            return redirect()
                ->route('tienda.recepciones')
                ->withErrors('Solo se pueden confirmar pedidos entregados (RECIBIDO_TIENDA).');
        }

        if (Auth::user()?->store_id && (int) $order->store_id !== (int) Auth::user()->store_id) {
            return redirect()
                ->route('tienda.recepciones')
                ->withErrors('Este pedido no pertenece a tu tienda.');
        }

        $order->update([
            'status' => Order::STATUS_CERRADO,
            'completed_at' => now(),
        ]);

        $order->recordMovement(
            Order::STATUS_CERRADO,
            'Pedido recibido y cerrado en tienda',
        );

        foreach ($order->shipments as $shipment) {
            if ($shipment->status !== Shipment::STATUS_ENTREGADO) {
                continue;
            }
            $allClosed = $shipment->orders
                ->whereIn('status', [Order::STATUS_CERRADO])
                ->count() === $shipment->orders->count();

            if ($allClosed) {
                $shipment->update(['status' => Shipment::STATUS_CERRADO]);
                $shipment->recordMovement(
                    Shipment::STATUS_CERRADO,
                    'Embarque cerrado: todos los pedidos recibidos en tienda',
                );
            }
        }

        return redirect()
            ->route('tienda.recepciones')
            ->with('success', "Pedido {$order->order_number} cerrado correctamente.");
    }
}