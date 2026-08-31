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
            $table->string('first_name')->nullable()->after('id');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('suffix')->nullable()->after('last_name');
            $table->string('purok')->nullable()->after('address');
            $table->enum('declared_type', ['resident', 'non_resident'])->nullable()->after('purok');
            $table->text('rejection_reason')->nullable()->after('verified_by');
        });

        // Backfill: move existing single "name" into first/last as a best effort
        foreach (DB::table('users')->get() as $u) {
            $parts = preg_split('/\s+/', trim($u->name));
            $first = array_shift($parts) ?: $u->name;
            $last  = count($parts) ? array_pop($parts) : '';
            $middle = count($parts) ? implode(' ', $parts) : null;

            DB::table('users')->where('id', $u->id)->update([
                'first_name'  => $first,
                'middle_name' => $middle,
                'last_name'   => $last,
            ]);
        }

        // Make the core name parts required now that they're filled
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name', 'middle_name', 'last_name', 'suffix',
                'purok', 'declared_type', 'rejection_reason',
            ]);
        });
    }
};