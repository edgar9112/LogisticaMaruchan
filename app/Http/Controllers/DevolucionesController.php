<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DevolucionesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:tienda,almacen,admin']);
    }

    public function index(Request $request)
    {
        $query = OrderReturn::with(['order.store', 'requester', 'items.orderItem.presentation'])
            ->orderByDesc('requested_at');

        if ($request->filled('estado')) {
            $query->where('status', $request->estado);
        }

        if (Auth::user()->role === 'almacen') {
            $query->whereNot('status', OrderReturn::STATUS_RECIBIDA);
        } elseif (Auth::user()?->store_id) {
            $query->whereHas('order', fn ($q) => $q->where('store_id', Auth::user()->store_id));
        }

        $returns = $query->get();

        return view('devoluciones.index', [
            'returns' => $returns,
            'statuses' => OrderReturn::STATUSES,
            'activeStatus' => $request->input('estado'),
        ]);
    }

    public function create(Order $order)
    {
        if (! in_array($order->status, [Order::STATUS_RECIBIDO_TIENDA, Order::STATUS_CERRADO])) {
            return redirect()
                ->route('devoluciones.index')
                ->withErrors('Solo se pueden devolver pedidos entregados o cerrados en tienda.');
        }

        if (Auth::user()?->store_id && (int) $order->store_id !== (int) Auth::user()->store_id) {
            return redirect()
                ->route('devoluciones.index')
                ->withErrors('Este pedido no pertenece a tu tienda.');
        }

        $order->load(['store', 'items.presentation']);

        return view('devoluciones.create', [
            'order' => $order,
            'reasons' => OrderReturn::REASONS,
            'conditions' => OrderReturn::CONDITIONS,
        ]);
    }

    public function store(Request $request, Order $order)
    {
        if (! in_array($order->status, [Order::STATUS_RECIBIDO_TIENDA, Order::STATUS_CERRADO])) {
            return redirect()
                ->route('devoluciones.index')
                ->withErrors('Solo se pueden devolver pedidos entregados o cerrados en tienda.');
        }

        if (Auth::user()?->store_id && (int) $order->store_id !== (int) Auth::user()->store_id) {
            return redirect()
                ->route('devoluciones.index')
                ->withErrors('Este pedido no pertenece a tu tienda.');
        }

        $items = $order->items->keyBy('id');

        $validated = $request->validate([
            'reason_type' => ['required', Rule::in(array_keys(OrderReturn::REASONS))],
            'note' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.condition' => ['nullable', Rule::in(array_keys(OrderReturn::CONDITIONS))],
        ]);

        $toReturn = [];

        foreach (array_keys($validated['items']) as $orderItemId) {
            $item = $items->get((int) $orderItemId);

            if (! $item) {
                return back()->withErrors('Uno de los productos no pertenece a este pedido.');
            }

            $available = (int) $item->quantity_received ?: (int) $item->quantity_prepared;

            if ($validated['items'][$orderItemId]['quantity'] > $available) {
                return back()->withErrors(
                    "La cantidad a devolver de \"{$item->presentation->name}\" supera lo recibido ($available)."
                );
            }

            $toReturn[] = [
                'order_item_id' => $item->id,
                'quantity' => $validated['items'][$orderItemId]['quantity'],
                'condition' => $validated['items'][$orderItemId]['condition'] ?? null,
            ];
        }

        $devolucion = $order->returns()->create([
            'status' => OrderReturn::STATUS_SOLICITADA,
            'reason_type' => $validated['reason_type'],
            'note' => $validated['note'] ?? null,
            'requested_by' => auth()->id(),
            'requested_at' => now(),
        ]);

        $devolucion->items()->createMany($toReturn);

        $order->recordMovement(
            $order->status,
            'Devolución solicitada desde tienda',
            'Motivo: '.(OrderReturn::REASONS[$validated['reason_type']] ?? $validated['reason_type']),
            metadata: [
                'return_id' => $devolucion->id,
                'units' => array_sum(array_column($toReturn, 'quantity')),
            ],
        );

        return redirect()
            ->route('devoluciones.show', $devolucion)
            ->with('success', 'Devolución registrada correctamente.');
    }

    public function show(OrderReturn $devolucion)
    {
        $devolucion->load([
            'order.store',
            'order.warehouse',
            'items.orderItem.presentation',
            'requester',
            'receiver',
        ]);

        return view('devoluciones.show', compact('devolucion'));
    }

    public function recibir(OrderReturn $devolucion)
    {
        if ($devolucion->status === OrderReturn::STATUS_RECIBIDA) {
            return redirect()
                ->route('devoluciones.show', $devolucion)
                ->withErrors('Esta devolución ya fue recibida en almacén.');
        }

        $devolucion->update([
            'status' => OrderReturn::STATUS_RECIBIDA,
            'received_by' => auth()->id(),
            'received_at' => now(),
        ]);

        $devolucion->order->recordMovement(
            $devolucion->order->status,
            'Devolución recibida en almacén',
            'Motivo: '.$devolucion->reason_label,
            metadata: [
                'return_id' => $devolucion->id,
                'units' => $devolucion->items->sum('quantity'),
            ],
        );

        return redirect()
            ->route('devoluciones.show', $devolucion)
            ->with('success', 'Devolución recibida en almacén.');
    }
}