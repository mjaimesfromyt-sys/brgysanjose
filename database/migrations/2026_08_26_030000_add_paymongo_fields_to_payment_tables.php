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
                $table->string('paymongo_checkout_session_id')->nullable()->after('payment_status');
                $table->string('payment_channel')->nullable()->after('paymongo_checkout_session_id');
            });
        }
    }

    public function down(): void
    {
        foreach (['equipment_rentals', 'bookings', 'document_requests'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['paymongo_checkout_session_id', 'payment_channel']);
            });
        }
    }
};
