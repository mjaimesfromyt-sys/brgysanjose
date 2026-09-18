<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return; // SQLite has no ENUM; tests enforce values at the app layer.
        }

        DB::statement("ALTER TABLE equipment_rentals MODIFY status ENUM('pending', 'approved', 'rejected', 'released', 'returned', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("UPDATE equipment_rentals SET status = 'rejected' WHERE status = 'cancelled'");
        DB::statement("ALTER TABLE equipment_rentals MODIFY status ENUM('pending', 'approved', 'rejected', 'released', 'returned') NOT NULL DEFAULT 'pending'");
    }
};
