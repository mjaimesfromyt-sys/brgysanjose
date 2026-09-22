<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Track WHO collected each cash payment and WHEN, so the end-of-day
 * cash summary can be broken down per collector (accountability for
 * cash handled at the counter). Online (PayMongo) payments are
 * self-service and need no collector.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['bookings', 'document_requests', 'equipment_rentals'] as $table) {
            if (!Schema::hasColumn($table, 'collected_by')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('collected_by')->nullable()->after('payment_status');
                    $t->timestamp('collected_at')->nullable()->after('collected_by');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['bookings', 'document_requests', 'equipment_rentals'] as $table) {
            if (Schema::hasColumn($table, 'collected_by')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn(['collected_by', 'collected_at']);
                });
            }
        }
    }
};
