<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE equipment_rentals MODIFY status ENUM('pending', 'approved', 'rejected', 'released', 'returned', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("UPDATE equipment_rentals SET status = 'rejected' WHERE status = 'cancelled'");
        DB::statement("ALTER TABLE equipment_rentals MODIFY status ENUM('pending', 'approved', 'rejected', 'released', 'returned') NOT NULL DEFAULT 'pending'");
    }
};
