<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The document request form (and DocumentRequestController::store) collects
 * occupation / monthly_income / employed_since for employment and DSWD-type
 * certificates, but these columns only existed on the production database —
 * they were never captured in a migration, so fresh deploys failed with
 * "Unknown column 'occupation'". This restores the missing schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('document_requests', 'occupation')) {
            return; // Production DB already had these columns added manually.
        }

        Schema::table('document_requests', function (Blueprint $table) {
            $table->string('occupation')->nullable()->after('business_address');
            $table->decimal('monthly_income', 12, 2)->nullable()->after('occupation');
            $table->string('employed_since')->nullable()->after('monthly_income');
        });
    }

    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropColumn(['occupation', 'monthly_income', 'employed_since']);
        });
    }
};
