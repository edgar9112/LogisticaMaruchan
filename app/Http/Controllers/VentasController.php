<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\Presentation;
use App\Models\Store;
use App\Models\Warehouse;
use App\Support\Qr;

class VentasController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:ventas,admin']);
    }

    public function index()
    {
        $orders = Order::query()
            ->with(['store', 'creator'])
            ->withCount(['items' => fn ($q) => $q->selectRaw('sum(quantity_requested)')])
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('store', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('ordered_at')
            ->paginate(15)
            ->withQueryString();

        return view('ventas.pedidos.index', [
            'orders' => $orders,
            'statuses' => Order::STATUSES,
        ]);
    }

    public function create()
    {
        return view('ventas.pedidos.create', [
            'stores' => Store::where('active', true)->orderBy('name')->get(),
            'presentations' => Presentation::where('active', true)
                ->orderBy('presentation_type')
                ->orderBy('flavor')
                ->get(),
        ]);
    }

    public function store(OrderRequest $request)
    {
        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'store_id' => $request->store_id,
            'user_id' => auth()->id(),
            'warehouse_id' => Warehouse::where('active', true)->value('id'),
            'status' => Order::STATUS_CREADO,
            'notes' => $request->notes,
            'ordered_at' => now(),
        ]);

        foreach ($request->items as $item) {
            $order->items()->create([
                'presentation_id' => $item['presentation_id'],
                'quantity_requested' => $item['quantity'],
            ]);
        }

        $order->recordMovement(
            Order::STATUS_CREADO,
            'Pedido creado por ventas',
            metadata: ['store' => $order->store->name],
        );

        return redirect()
            ->route('ventas.pedidos.show', $order)
            ->with('success', "Pedido {$order->order_number} creado correctamente.");
    }

    public function show(Order $order)
    {
        $order->load([
            'store',
            'creator',
            'warehouse',
            'items.presentation',
            'movements.user',
        ]);

        return view('ventas.pedidos.show', [
            'order' => $order,
            'qr' => Qr::svgDataUri($order->order_number),
        ]);
    }
}