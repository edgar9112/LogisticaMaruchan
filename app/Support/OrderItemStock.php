<?php

namespace App\Support;

use App\Models\Order;
use App\Models\OrderItem;

class OrderItemStock
{
    /**
     * Unidades que aún están en el almacén: lo recibido menos lo preparado,
     * considerando solo pedidos en proceso dentro del almacén.
     */
    public static function sumInWarehouse(): int
    {
        $statuses = [
            Order::STATUS_EN_ALMACEN,
            Order::STATUS_RECIBIDO,
            Order::STATUS_CLASIFICADO,
            Order::STATUS_PREPARADO,
        ];

        return (int) OrderItem::query()
            ->whereHas('order', fn ($query) => $query->whereIn('status', $statuses))
            ->selectRaw('coalesce(sum(quantity_received) - sum(quantity_prepared), 0) as total')
            ->value('total');
    }
}