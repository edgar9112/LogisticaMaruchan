<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'plate', 'driver_name', 'capacity', 'active'])]
class Vehicle extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'capacity' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}