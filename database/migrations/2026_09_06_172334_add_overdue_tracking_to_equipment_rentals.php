<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_rentals', function (Blueprint $table) {
            $table->timestamp('due_at')->nullable()->after('released_at');
            $table->timestamp('overdue_notified_at')->nullable()->after('due_at');
            $table->decimal('late_fee', 8, 2)->default(0)->after('overdue_notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('equipment_rentals', function (Blueprint $table) {
            $table->dropColumn(['due_at', 'overdue_notified_at', 'late_fee']);
        });
    }
};
