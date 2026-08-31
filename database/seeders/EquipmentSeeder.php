<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name'        => 'Monoblock Chair',
                'description' => 'Standard plastic monoblock chair for events and gatherings.',
                'total_stock' => 200,
            ],
            [
                'name'        => 'Folding Table',
                'description' => 'Rectangular folding table, seats about 6–8 people.',
                'total_stock' => 40,
            ],
            [
                'name'        => 'Event Tent (10×10)',
                'description' => 'Portable canopy tent for outdoor events.',
                'total_stock' => 8,
            ],
        ];

        foreach ($items as $item) {
            Equipment::firstOrCreate(['name' => $item['name']], $item);
        }
    }
}
