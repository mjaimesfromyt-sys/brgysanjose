<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_rental_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->text('reason');
            $table->enum('type', ['cancellation', 'early_return']);
            $table->enum('status', ['requested', 'approved', 'rejected', 'refunded'])->default('requested');

            // What the resident was quoted at request time, and the final
            // figure the admin approves (they can adjust it down).
            $table->decimal('estimated_amount', 8, 2)->default(0);
            $table->decimal('amount', 8, 2)->nullable();

            $table->enum('refund_method', ['cash', 'online'])->nullable();
            $table->string('refund_reference')->nullable();   // PayMongo refund id, or a manual OR / GCash ref
            $table->string('paymongo_refund_id')->nullable();

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_remarks')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
    }
};
