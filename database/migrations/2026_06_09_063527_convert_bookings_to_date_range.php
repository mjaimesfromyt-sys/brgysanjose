<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add the new range columns (nullable for now so existing rows survive)
        Schema::table('bookings', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('facility_id');
            $table->date('end_date')->nullable()->after('start_date');
        });

        // Migrate existing single-date data into the range columns
        DB::statement('UPDATE bookings SET start_date = booking_date, end_date = booking_date');

        // Now make them required and drop the old column
        Schema::table('bookings', function (Blueprint $table) {
            $table->date('start_date')->nullable(false)->change();
            $table->date('end_date')->nullable(false)->change();
            $table->dropColumn('booking_date');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->date('booking_date')->nullable()->after('facility_id');
        });

        DB::statement('UPDATE bookings SET booking_date = start_date');

        Schema::table('bookings', function (Blueprint $table) {
            $table->date('booking_date')->nullable(false)->change();
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};