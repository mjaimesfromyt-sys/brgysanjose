<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Widening an ENUM is MySQL-only syntax. Production is MySQL, but the test
     * suite runs on SQLite, where enums are plain text columns with nothing to
     * widen — so the DDL is skipped there while the data migration still runs.
     */
    private function isMysql(): bool
    {
        return DB::getDriverName() === 'mysql';
    }

    public function up(): void
    {
        // Widen enum to include 'active', then migrate 'verified' -> 'active'
        if ($this->isMysql()) {
            DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending','active','verified','rejected') NOT NULL DEFAULT 'pending'");
        }

        DB::table('users')->where('status', 'verified')->update(['status' => 'active']);

        if ($this->isMysql()) {
            DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending','active','rejected') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if ($this->isMysql()) {
            DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending','verified','active','rejected') NOT NULL DEFAULT 'pending'");
        }

        DB::table('users')->where('status', 'active')->update(['status' => 'verified']);

        if ($this->isMysql()) {
            DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending'");
        }
    }
};