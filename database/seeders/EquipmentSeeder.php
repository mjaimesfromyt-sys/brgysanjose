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
                'name'        => 'TENT',
                'description' => NULL,
                'fee'         => 1000.00,
                'total_stock' => 2,
                'is_active'   => true,
            ],
            [
                'name'        => 'CHAIRS',
                'description' => NULL,
                'fee'         => 5.00,
                'total_stock' => 130,
                'is_active'   => true,
            ],
            [
                'name'        => 'TABLES',
                'description' => NULL,
                'fee'         => 99.99,
                'total_stock' => 9,
                'is_active'   => false,
            ],
            [
                'name'        => 'DURABLE TABLES',
                'description' => NULL,
                'fee'         => 100.00,
                'total_stock' => 9,
                'is_active'   => true,
            ],
            [
                'name'        => 'CEMENT MIXER',
                'description' => NULL,
                'fee'         => 600.00,
                'total_stock' => 1,
                'is_active'   => true,
            ],
        ];

        foreach ($items as $item) {
            Equipment::firstOrCreate(['name' => $item['name']], $item);
        }
    }
}
