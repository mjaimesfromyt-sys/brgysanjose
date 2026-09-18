<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('birthdate')->nullable()->after('contact_no');
            $table->string('gender', 20)->nullable()->after('birthdate');
            $table->string('civil_status', 30)->nullable()->after('gender');
            $table->string('citizenship', 100)->nullable()->default('Filipino')->after('civil_status');
            $table->string('religion', 100)->nullable()->after('citizenship');
            $table->string('length_of_stay', 100)->nullable()->after('purok');
            $table->boolean('is_voter')->default(false)->after('length_of_stay');
            $table->string('id_type', 100)->nullable()->after('is_voter');
            $table->string('id_number', 100)->nullable()->after('id_type');
            $table->string('id_photo')->nullable()->after('id_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'birthdate', 'gender', 'civil_status', 'citizenship', 'religion',
                'length_of_stay', 'is_voter', 'id_type', 'id_number', 'id_photo',
            ]);
        });
    }
};
