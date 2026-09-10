<?php

namespace Database\Seeders;

use App\Models\Presentation;
use Illuminate\Database\Seeder;

class PresentationSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            ['vaso', 'Pollo', 24, 'MAR-VAS-POL-24'],
            ['vaso', 'Res', 24, 'MAR-VAS-RES-24'],
            ['vaso', 'Camarón', 24, 'MAR-VAS-CAM-24'],
            ['vaso', 'Cangrejo', 24, 'MAR-VAS-CAN-24'],
            ['bolsa', 'Pollo', 48, 'MAR-BOL-POL-48'],
            ['bolsa', 'Res', 48, 'MAR-BOL-RES-48'],
            ['bolsa', 'Camarón', 48, 'MAR-BOL-CAM-48'],
            ['caja', 'Mixta 12 piezas', 12, 'MAR-CAJ-MIX-12'],
            ['caja', 'Mixta 24 piezas', 24, 'MAR-CAJ-MIX-24'],
            ['caja', 'Mixta 48 piezas', 48, 'MAR-CAJ-MIX-48'],
        ];

        foreach ($catalog as [$type, $flavor, $pieces, $sku]) {
            Presentation::firstOrCreate(
                ['sku' => $sku],
                [
                    'name' => 'Sopa Maruchan',
                    'presentation_type' => $type,
                    'flavor' => $flavor,
                    'pieces_per_box' => $pieces,
                ],
            );
        }
    }
}