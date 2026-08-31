<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['equipment_rentals', 'bookings', 'document_requests'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->renameColumn('gcash_reference', 'payment_reference');
            });
        }
    }

    public function down(): void
    {
        foreach (['equipment_rentals', 'bookings', 'document_requests'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->renameColumn('payment_reference', 'gcash_reference');
            });
        }
    }
};
