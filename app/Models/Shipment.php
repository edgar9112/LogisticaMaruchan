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
    'shipment_number', 'origin_warehouse_id', 'destination_store_id',
    'vehicle_id', 'driver_name', 'status', 'notes', 'scheduled_at',
    'departed_at', 'arrived_at',
])]
class Shipment extends Model
{
    use HasFactory;

    public const STATUS_PREPARADO = 'PREPARADO';
    public const STATUS_CARGADO = 'CARGADO';
    public const STATUS_EN_TRANSITO = 'EN_TRANSITO';
    public const STATUS_ENTREGADO = 'ENTREGADO';
    public const STATUS_CERRADO = 'CERRADO';

    public const STATUSES = [
        self::STATUS_PREPARADO,
        self::STATUS_CARGADO,
        self::STATUS_EN_TRANSITO,
        self::STATUS_ENTREGADO,
        self::STATUS_CERRADO,
    ];

    /**
     * Genera un folio único con la forma EMB-YYYYMMDD-####.
     */
    public static function generateShipmentNumber(): string
    {
        $prefix = 'EMB-'.now()->format('Ymd').'-';
        $last = static::query()
            ->where('shipment_number', 'like', $prefix.'%')
            ->orderByDesc('shipment_number')
            ->value('shipment_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Registra un movimiento de trazabilidad sobre este embarque.
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

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'departed_at' => 'datetime',
            'arrived_at' => 'datetime',
        ];
    }

    public function originWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'origin_warehouse_id');
    }

    public function destinationStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'destination_store_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'shipment_items')
            ->withTimestamps();
    }

    public function movements(): MorphMany
    {
        return $this->morphMany(Movement::class, 'trackable');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }
}