<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_allocations')) {
            Schema::create('payment_allocations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
                $table->string('invoice_type', 20)->default('sale');
                $table->foreignId('invoice_id')->nullable()->constrained('sales')->cascadeOnDelete();
                $table->foreignId('repair_job_id')->nullable()->constrained('repair_jobs')->cascadeOnDelete();
                $table->decimal('allocated_amount', 12, 2);
                $table->timestamps();

                $table->index(['invoice_id', 'payment_id']);
                $table->index(['repair_job_id', 'payment_id']);
            });

            return;
        }

        if (! Schema::hasColumn('payment_allocations', 'payment_id')) {
            Schema::table('payment_allocations', function (Blueprint $table) {
                $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('payment_allocations', 'invoice_type')) {
            Schema::table('payment_allocations', function (Blueprint $table) {
                $table->string('invoice_type', 20)->default('sale')->after('payment_id');
            });
        }

        if (! Schema::hasColumn('payment_allocations', 'invoice_id')) {
            Schema::table('payment_allocations', function (Blueprint $table) {
                $table->foreignId('invoice_id')
                    ->nullable()
                    ->after('invoice_type')
                    ->constrained('sales')
                    ->cascadeOnDelete();
            });
        } elseif (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE payment_allocations MODIFY invoice_id BIGINT UNSIGNED NULL');
        }

        if (! Schema::hasColumn('payment_allocations', 'repair_job_id')) {
            Schema::table('payment_allocations', function (Blueprint $table) {
                $table->foreignId('repair_job_id')
                    ->nullable()
                    ->after('invoice_id')
                    ->constrained('repair_jobs')
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('payment_allocations', 'allocated_amount')) {
            Schema::table('payment_allocations', function (Blueprint $table) {
                $table->decimal('allocated_amount', 12, 2)->default(0);
            });
        }
    }

    public function down(): void
    {
        // Keep historical payment allocation data safe on rollback.
    }
};
