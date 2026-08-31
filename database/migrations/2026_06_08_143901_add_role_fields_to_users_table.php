<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'resident', 'official'])
                  ->default('resident')
                  ->after('email');
            $table->enum('status', ['pending', 'verified', 'rejected'])
                  ->default('pending')
                  ->after('role');
            $table->string('contact_no')->nullable()->after('status');
            $table->text('address')->nullable()->after('contact_no');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status', 'contact_no', 'address']);
        });
    }
};