<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'status'], 'users_role_status_index');
        });

        Schema::table('document_requests', function (Blueprint $table) {
            $table->index('status', 'document_requests_status_index');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->index('status', 'bookings_status_index');
        });

        Schema::table('equipment_rentals', function (Blueprint $table) {
            $table->index('status', 'equipment_rentals_status_index');
        });

        Schema::table('refund_requests', function (Blueprint $table) {
            $table->index('status', 'refund_requests_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropIndex('users_role_status_index'));
        Schema::table('document_requests', fn (Blueprint $t) => $t->dropIndex('document_requests_status_index'));
        Schema::table('bookings', fn (Blueprint $t) => $t->dropIndex('bookings_status_index'));
        Schema::table('equipment_rentals', fn (Blueprint $t) => $t->dropIndex('equipment_rentals_status_index'));
        Schema::table('refund_requests', fn (Blueprint $t) => $t->dropIndex('refund_requests_status_index'));
    }
};
