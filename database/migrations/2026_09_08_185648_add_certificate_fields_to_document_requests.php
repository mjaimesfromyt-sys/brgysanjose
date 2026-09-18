<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            // Land / property certificates
            $table->string('lot_no')->nullable();
            $table->string('tax_dec_no')->nullable();
            $table->string('cad_no')->nullable();
            $table->string('land_area')->nullable();
            $table->string('land_owner')->nullable();
            $table->string('owner_spouse')->nullable();
            $table->string('tenant_name')->nullable();

            // Third-party requester (kung dili ang resident ang subject)
            $table->string('requester_name')->nullable();

            // Deceased-related certificates
            $table->string('deceased_name')->nullable();
            $table->date('date_died')->nullable();
            $table->string('burial_place')->nullable();

            // PWD / Senior Citizen
            $table->string('control_no')->nullable();
            $table->string('disability_type')->nullable();

            // Solo parent
            $table->string('solo_parent_since')->nullable();

            // On-duty official
            $table->string('on_duty_name')->nullable();
            $table->string('on_duty_label')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropColumn([
                'lot_no', 'tax_dec_no', 'cad_no', 'land_area',
                'land_owner', 'owner_spouse', 'tenant_name',
                'requester_name', 'deceased_name', 'date_died', 'burial_place',
                'control_no', 'disability_type', 'solo_parent_since',
                'on_duty_name', 'on_duty_label',
            ]);
        });
    }
};
