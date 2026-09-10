<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouse = Warehouse::firstOrCreate(
            ['code' => 'ALM-01'],
            ['name' => 'Almacén Central', 'address' => 'Parque Industrial 1'],
        );

        foreach (['A', 'B', 'C', 'D'] as $zone) {
            WarehouseLocation::firstOrCreate(
                ['warehouse_id' => $warehouse->id, 'code' => "ZONA-{$zone}"],
                ['description' => "Zona {$zone} de almacenamiento"],
            );
        }
    }
}