<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'profile_picture')) {
                $table->string('profile_picture')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'contact_no')) {
                $table->string('contact_no')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('contact_no');
            }
            if (!Schema::hasColumn('users', 'purok')) {
                $table->string('purok')->nullable()->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {$table->dropColumn(['profile_picture', 'contact_no', 'address', 'purok']);
        });
    }
};