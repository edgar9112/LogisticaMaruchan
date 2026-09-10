<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'order_number', 'store_id', 'user_id', 'warehouse_id',
    'status', 'notes', 'ordered_at', 'received_at', 'prepared_at',
    'shipped_at', 'completed_at',
])]
class Order extends Model
{
    use HasFactory;

    public const STATUS_CREADO = 'CREADO';
    public const STATUS_CONFIRMADO = 'CONFIRMADO';
    public const STATUS_EN_ALMACEN = 'EN_ALMACEN';
    public const STATUS_RECIBIDO = 'RECIBIDO';
    public const STATUS_CLASIFICADO = 'CLASIFICADO';
    public const STATUS_PREPARADO = 'PREPARADO';
    public const STATUS_ASIGNADO_EMBARQUE = 'ASIGNADO_EMBARQUE';
    public const STATUS_CARGADO = 'CARGADO';
    public const STATUS_EN_TRANSITO = 'EN_TRANSITO';
    public const STATUS_RECIBIDO_TIENDA = 'RECIBIDO_TIENDA';
    public const STATUS_CERRADO = 'CERRADO';

    public const STATUSES = [
        self::STATUS_CREADO,
        self::STATUS_CONFIRMADO,
        self::STATUS_EN_ALMACEN,
        self::STATUS_RECIBIDO,
        self::STATUS_CLASIFICADO,
        self::STATUS_PREPARADO,
        self::STATUS_ASIGNADO_EMBARQUE,
        self::STATUS_CARGADO,
        self::STATUS_EN_TRANSITO,
        self::STATUS_RECIBIDO_TIENDA,
        self::STATUS_CERRADO,
    ];

    protected function casts(): array
    {
        return [
            'ordered_at' => 'datetime',
            'received_at' => 'datetime',
            'prepared_at' => 'datetime',
            'shipped_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Genera un folio único con la forma PED-YYYYMMDD-####.
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'PED-'.now()->format('Ymd').'-';
        $last = static::query()
            ->where('order_number', 'like', $prefix.'%')
            ->orderByDesc('order_number')
            ->value('order_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Registra un movimiento de trazabilidad sobre este pedido.
     */
    public function recordMovement(
        string $state,
        string $action,
        ?string $description = null,
        array $metadata = [],
        ?User $user = null,
    ): Movement {
        return $this->movements()->create([
            'user_id' => $user?->id ?? auth()->id(),
            'state' => $state,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata ?: null,
        ]);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function movements(): MorphMany
    {
        return $this->morphMany(Movement::class, 'trackable');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    public function shipments(): BelongsToMany
    {
        return $this->belongsToMany(Shipment::class, 'shipment_items')
            ->withTimestamps();
    }
}