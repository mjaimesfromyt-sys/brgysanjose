<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            ['name' => 'Barangay Hall', 'description' => 'Main multi-purpose hall for events and gatherings.', 'capacity' => 200, 'fee' => 20.00],
            ['name' => 'Covered Court', 'description' => 'Outdoor covered basketball court.', 'capacity' => 300, 'fee' => 20.00],
            ['name' => 'Conference Room', 'description' => 'Small room for meetings.', 'capacity' => 30, 'fee' => 20.00],
        ];

        foreach ($facilities as $f) {
            Facility::firstOrCreate(['name' => $f['name']], $f);
        }
    }
}