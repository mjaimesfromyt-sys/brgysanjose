<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Only the admin account is seeded. Facilities, equipment and document
     * types are managed by the barangay staff through the admin panel, not
     * seeded. Run FacilitySeeder / EquipmentSeeder / TransactionTypeSeeder
     * by hand if you want the sample catalog back.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
        ]);
    }
}
