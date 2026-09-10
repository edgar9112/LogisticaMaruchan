<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            StoreSeeder::class,
            WarehouseSeeder::class,
            PresentationSeeder::class,
            VehicleSeeder::class,
            UserSeeder::class,
        ]);
    }
}