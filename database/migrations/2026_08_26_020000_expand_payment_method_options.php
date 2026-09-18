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

        foreach (['equipment_rentals', 'bookings', 'document_requests'] as $table) {
            DB::statement("ALTER TABLE {$table} MODIFY payment_method ENUM('cash', 'gcash', 'paymaya', 'bank_transfer') NULL");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        foreach (['equipment_rentals', 'bookings', 'document_requests'] as $table) {
            DB::statement("ALTER TABLE {$table} MODIFY payment_method ENUM('cash', 'gcash') NULL");
        }
    }
};
