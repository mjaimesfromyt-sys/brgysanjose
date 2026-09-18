<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seeds the full baseline catalog: admin account, barangay settings,
     * document types (with slugs + requirements), equipment, and facilities.
     * All seeders use firstOrCreate, so re-running is safe.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            BarangaySettingsSeeder::class,
            TransactionTypeSeeder::class,
            EquipmentSeeder::class,
            FacilitySeeder::class,
        ]);
    }
}
