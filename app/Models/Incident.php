<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id', 'shipment_id', 'user_id', 'type',
    'description', 'evidence_path', 'occurred_at',
])]
class Incident extends Model
{
    use HasFactory;

    public const TYPE_FALTANTE = 'faltante';
    public const TYPE_SOBRANTE = 'sobrante';
    public const TYPE_DANADO = 'danado';
    public const TYPE_MERCANCIA_NO_LOCALIZADA = 'mercancia_no_localizada';
    public const TYPE_DIFERENCIA = 'diferencia_de_cantidad';
    public const TYPE_ENTREGA_RECHAZADA = 'entrega_rechazada';

    public const TYPE_PERCANCE = 'percance';
    public const TYPE_PARADA = 'parada';
    public const TYPE_RETRASO = 'retraso';
    public const TYPE_NOTA = 'nota';

    public const ROUTE_INCIDENT_TYPES = [
        self::TYPE_PERCANCE => 'Percance (desperfecto/accidente)',
        self::TYPE_PARADA => 'Parada controlada',
        self::TYPE_RETRASO => 'Retraso (tráfico/clima)',
        self::TYPE_NOTA => 'Nota general',
    ];

    public function getTypeLabelAttribute(): string
    {
        return self::ROUTE_INCIDENT_TYPES[$this->type]
            ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}