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
            return;
        }

        if (Schema::hasColumn('payment_allocations', 'invoice_id')) {
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE payment_allocations MODIFY invoice_id BIGINT UNSIGNED NULL');
            } else {
                Schema::table('payment_allocations', function (Blueprint $table) {
                    $table->unsignedBigInteger('invoice_id')->nullable()->change();
                });
            }
        }

        Schema::table('payment_allocations', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_allocations', 'invoice_type')) {
                $table->string('invoice_type', 20)->default('sale')->after('payment_id');
            }

            if (! Schema::hasColumn('payment_allocations', 'repair_job_id')) {
                $table->foreignId('repair_job_id')
                    ->nullable()
                    ->after('invoice_id')
                    ->constrained('repair_jobs')
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('payment_allocations')) {
            return;
        }

        Schema::table('payment_allocations', function (Blueprint $table) {
            if (Schema::hasColumn('payment_allocations', 'repair_job_id')) {
                $table->dropConstrainedForeignId('repair_job_id');
            }

            if (Schema::hasColumn('payment_allocations', 'invoice_type')) {
                $table->dropColumn('invoice_type');
            }
        });
    }
};
