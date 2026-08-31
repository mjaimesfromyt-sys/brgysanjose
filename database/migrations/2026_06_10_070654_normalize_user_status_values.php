<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Widen enum to include 'active', then migrate 'verified' -> 'active'
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending','active','verified','rejected') NOT NULL DEFAULT 'pending'");
        DB::table('users')->where('status', 'verified')->update(['status' => 'active']);
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending','active','rejected') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending','verified','active','rejected') NOT NULL DEFAULT 'pending'");
        DB::table('users')->where('status', 'active')->update(['status' => 'verified']);
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending'");
    }
};