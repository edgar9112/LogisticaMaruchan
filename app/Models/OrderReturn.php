<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_id', 'status', 'reason_type', 'note',
    'requested_by', 'received_by', 'requested_at', 'received_at',
])]
class OrderReturn extends Model
{
    use HasFactory;

    protected $table = 'returns';

    public const STATUS_SOLICITADA = 'SOLICITADA';
    public const STATUS_EN_RUTA = 'EN_RUTA_DEVOLUCION';
    public const STATUS_RECIBIDA = 'RECIBIDA_ALMACEN';

    public const STATUSES = [
        self::STATUS_SOLICITADA,
        self::STATUS_EN_RUTA,
        self::STATUS_RECIBIDA,
    ];

    public const STATUS_LABELS = [
        self::STATUS_SOLICITADA => 'Solicitada',
        self::STATUS_EN_RUTA => 'En ruta de retorno',
        self::STATUS_RECIBIDA => 'Recibida en almacén',
    ];

    public const REASONS = [
        'mercancia_danada' => 'Mercancía dañada',
        'producto_equivocado' => 'Producto equivocado',
        'caducidad_proxima' => 'Caducidad próxima',
        'sobrante' => 'Unidades sobrantes',
        'otro' => 'Otro',
    ];

    public const CONDITIONS = [
        'buena' => 'En buen estado',
        'danada' => 'Dañada / no apta',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function getReasonLabelAttribute(): string
    {
        return self::REASONS[$this->reason_type] ?? $this->reason_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? str_replace('_', ' ', $this->status);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderReturnItem::class, 'return_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}