<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('resident_type', ['resident', 'non_resident'])
                  ->nullable()
                  ->after('status');
            $table->timestamp('verified_at')->nullable()->after('resident_type');
            $table->foreignId('verified_by')->nullable()->after('verified_at')
                  ->constrained('users')->nullOnDelete();
        });

        // Existing admin accounts: treat as active residents
        DB::table('users')->where('role', 'admin')->update([
            'resident_type' => 'resident',
            'verified_at'   => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn(['resident_type', 'verified_at']);
        });
    }
};