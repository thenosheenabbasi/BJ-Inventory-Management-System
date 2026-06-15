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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_code')->unique();
            $table->string('full_name');
            $table->string('phone');
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('city')->nullable();
            $table->string('country')->default('UAE');
            $table->enum('customer_type', ['walk_in', 'repair_customer', 'purchase_customer', 'both'])->default('walk_in');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
