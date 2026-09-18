<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangaySettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'captain_name'      => 'JOSEFINA C. GURREA',
            'secretary_name'    => 'HANNAH JOY B. CREDO',
            'barangay_address'  => 'PUROK 5, SAN JOSE, TALIBON, BOHOL',
            'barangay_email'    => 'blgusanjosetalibon1910@gmail.com',
            'barangay_facebook' => 'Barangay San Jose - Official',
        ];

        foreach ($settings as $key => $value) {
            DB::table('barangay_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
