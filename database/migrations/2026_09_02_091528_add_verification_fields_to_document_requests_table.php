<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerificationFieldsToDocumentRequestsTable extends Migration
{
    public function up(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('document_requests', 'verification_code')) {
                $table->string('verification_code')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('document_requests', 'control_number')) {
                $table->string('control_number')->nullable()->after('verification_code');
            }
            if (!Schema::hasColumn('document_requests', 'issued_at')) {
                $table->timestamp('issued_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('document_requests', 'valid_until')) {
                $table->date('valid_until')->nullable()->after('issued_at');
            }
            if (!Schema::hasColumn('document_requests', 'purpose')) {
                $table->string('purpose')->nullable()->after('valid_until');
            }
            if (!Schema::hasColumn('document_requests', 'or_number')) {
                $table->string('or_number')->nullable()->after('purpose');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropColumn(['verification_code', 'control_number', 'issued_at', 'valid_until', 'purpose', 'or_number']);
        });
    }
}