<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'store_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLES = [
        'admin',
        'ventas',
        'almacen',
        'logistica',
        'transporte',
        'tienda',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function ordersCreated(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasRole(string $role): bool
    {
        return $this->isAdmin() || $this->role === $role;
    }

    /**
     * Ruta a la que llega cada rol tras iniciar sesión.
     */
    public function homeRoute(): string
    {
        return match ($this->role) {
            'ventas' => 'ventas.pedidos',
            'almacen' => 'almacen.pendientes',
            'logistica' => 'embarques.index',
            'transporte' => 'viajes.index',
            'tienda' => 'tienda.recepciones',
            default => 'dashboard',
        };
    }
}