<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            [
                'name'        => 'Covered Court/Basketball Court',
                'description' => NULL,
                'capacity'    => 300,
                'fee'         => 50.00,
                'is_active'   => true,
            ],
        ];

        foreach ($facilities as $f) {
            Facility::firstOrCreate(['name' => $f['name']], $f);
        }
    }
}
