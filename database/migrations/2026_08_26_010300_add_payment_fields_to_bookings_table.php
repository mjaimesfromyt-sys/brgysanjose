<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('claim_code')->nullable()->unique()->after('purpose');
            $table->enum('payment_method', ['cash', 'gcash'])->nullable()->after('claim_code');
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid')->after('payment_method');
            $table->string('gcash_reference')->nullable()->after('payment_status');
            $table->decimal('amount_due', 8, 2)->default(0)->after('gcash_reference');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['claim_code', 'payment_method', 'payment_status', 'gcash_reference', 'amount_due']);
        });
    }
};
