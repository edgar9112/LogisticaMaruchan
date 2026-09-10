<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::query()->value('id');

        $users = [
            ['name' => 'Administrador', 'email' => 'admin@maruchan.test', 'role' => 'admin'],
            ['name' => 'Ventas', 'email' => 'ventas@maruchan.test', 'role' => 'ventas'],
            ['name' => 'Almacén', 'email' => 'almacen@maruchan.test', 'role' => 'almacen'],
            ['name' => 'Logística', 'email' => 'logistica@maruchan.test', 'role' => 'logistica'],
            ['name' => 'Transporte', 'email' => 'transporte@maruchan.test', 'role' => 'transporte'],
            ['name' => 'Tienda', 'email' => 'tienda@maruchan.test', 'role' => 'tienda'],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'store_id' => $user['role'] === 'admin' ? null : $store,
                    'password' => Hash::make('password'),
                ],
            );
        }
    }
}