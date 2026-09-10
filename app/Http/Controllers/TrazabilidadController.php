<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\Qr;
use Illuminate\Http\Request;

class TrazabilidadController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,ventas,almacen,logistica']);
    }

    public function index(Request $request)
    {
        $order = null;

        if ($request->filled('order_number')) {
            $term = mb_strtoupper(trim($request->order_number));

            $order = Order::with([
                'store',
                'warehouse',
                'creator',
                'items.presentation',
                'movements.user',
                'incidents.user',
            ])->where(fn ($query) => $query
                ->where('order_number', $term)
                ->orWhere('order_number', 'like', "%{$term}%"))
                ->orderByDesc('ordered_at')
                ->first();
        }

        return view('trazabilidad.index', [
            'order' => $order,
            'search' => $request->order_number ?? '',
            'qr' => $order ? Qr::svgDataUri($order->order_number) : null,
        ]);
    }
}