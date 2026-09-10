<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'trackable_type', 'trackable_id', 'user_id', 'state',
    'action', 'description', 'metadata',
])]
class Movement extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}