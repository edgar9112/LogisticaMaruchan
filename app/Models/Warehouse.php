<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'address', 'active'])]
class Warehouse extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function locations(): HasMany
    {
        return $this->hasMany(WarehouseLocation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function shipmentsOutbound(): HasMany
    {
        return $this->hasMany(Shipment::class, 'origin_warehouse_id');
    }
}