<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = [
            ['code' => 'VEH-01', 'plate' => 'ABC-123', 'driver_name' => 'Juan Pérez', 'capacity' => 1200],
            ['code' => 'VEH-02', 'plate' => 'DEF-456', 'driver_name' => 'María López', 'capacity' => 800],
            ['code' => 'VEH-03', 'plate' => 'GHI-789', 'driver_name' => 'Carlos Gómez', 'capacity' => 1500],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::firstOrCreate(['code' => $vehicle['code']], $vehicle);
        }
    }
}