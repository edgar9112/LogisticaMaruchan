<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id', 'presentation_id', 'quantity_requested',
    'quantity_received', 'quantity_prepared',
])]
class OrderItem extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantity_requested' => 'integer',
            'quantity_received' => 'integer',
            'quantity_prepared' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function presentation(): BelongsTo
    {
        return $this->belongsTo(Presentation::class);
    }
}