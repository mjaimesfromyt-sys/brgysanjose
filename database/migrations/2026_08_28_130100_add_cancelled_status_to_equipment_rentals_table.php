<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** ENUM widening is MySQL-only; SQLite (used by the test suite) has nothing to widen. */
    private function isMysql(): bool
    {
        return DB::getDriverName() === 'mysql';
    }

    public function up(): void
    {
        if (! $this->isMysql()) {
            return;
        }

        DB::statement("ALTER TABLE equipment_rentals MODIFY status ENUM('pending', 'approved', 'rejected', 'released', 'returned', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (! $this->isMysql()) {
            return;
        }

        DB::statement("UPDATE equipment_rentals SET status = 'rejected' WHERE status = 'cancelled'");
        DB::statement("ALTER TABLE equipment_rentals MODIFY status ENUM('pending', 'approved', 'rejected', 'released', 'returned') NOT NULL DEFAULT 'pending'");
    }
};
