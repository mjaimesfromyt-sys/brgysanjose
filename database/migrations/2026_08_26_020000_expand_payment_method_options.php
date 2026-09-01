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

        foreach (['equipment_rentals', 'bookings', 'document_requests'] as $table) {
            DB::statement("ALTER TABLE {$table} MODIFY payment_method ENUM('cash', 'gcash', 'paymaya', 'bank_transfer') NULL");
        }
    }

    public function down(): void
    {
        if (! $this->isMysql()) {
            return;
        }

        foreach (['equipment_rentals', 'bookings', 'document_requests'] as $table) {
            DB::statement("ALTER TABLE {$table} MODIFY payment_method ENUM('cash', 'gcash') NULL");
        }
    }
};
