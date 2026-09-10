<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            ['code' => 'TDE-001', 'name' => 'Tienda Centro', 'address' => 'Av. Juárez 123, Centro'],
            ['code' => 'TDE-002', 'name' => 'Tienda Norte', 'address' => 'Calz. Norte 456'],
            ['code' => 'TDE-003', 'name' => 'Tienda Sur', 'address' => 'Blvd. Sur 789'],
            ['code' => 'TDE-004', 'name' => 'Tienda Oriente', 'address' => 'Av. Oriente 101'],
            ['code' => 'TDE-005', 'name' => 'Tienda Poniente', 'address' => 'Av. Poniente 202'],
        ];

        foreach ($stores as $store) {
            Store::firstOrCreate(['code' => $store['code']], $store);
        }
    }
}