<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['equipment_rentals', 'bookings', 'document_requests'] as $table) {
            DB::statement("ALTER TABLE {$table} MODIFY payment_method ENUM('cash', 'gcash', 'paymaya', 'bank_transfer') NULL");
        }
    }

    public function down(): void
    {
        foreach (['equipment_rentals', 'bookings', 'document_requests'] as $table) {
            DB::statement("ALTER TABLE {$table} MODIFY payment_method ENUM('cash', 'gcash') NULL");
        }
    }
};
