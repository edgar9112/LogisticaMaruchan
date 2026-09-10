<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'presentation_type', 'flavor', 'pieces_per_box', 'sku', 'active'])]
class Presentation extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'pieces_per_box' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}