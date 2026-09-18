<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return; // SQLite has no ENUM; tests enforce values at the app layer.
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','resident','official','super_admin') NOT NULL DEFAULT 'resident'");
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending','active','rejected','inactive') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','resident','official') NOT NULL DEFAULT 'resident'");
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending','active','rejected') NOT NULL DEFAULT 'pending'");
    }
};
