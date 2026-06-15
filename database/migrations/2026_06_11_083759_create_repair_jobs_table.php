<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('repair_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('repair_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->text('battery_details');
            $table->text('issue_description');
            $table->text('technician_notes')->nullable();
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->decimal('advance_payment', 12, 2)->default(0);
            $table->date('expected_delivery_date')->nullable();
            $table->enum('status', [
                'received',
                'diagnosis',
                'waiting_approval',
                'repairing',
                'ready_for_pickup',
                'delivered',
            ])->default('received');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_jobs');
    }
};
