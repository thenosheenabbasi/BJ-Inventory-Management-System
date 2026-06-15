<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'repair_job_id')) {
                $table->foreignId('repair_job_id')->nullable()->after('id')->constrained('repair_jobs')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('payments', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('repair_job_id')->constrained('customers')->nullOnDelete();
            }

            if (! Schema::hasColumn('payments', 'payment_number')) {
                $table->string('payment_number')->nullable()->unique()->after('customer_id');
            }

            if (! Schema::hasColumn('payments', 'payment_type')) {
                $table->enum('payment_type', ['advance', 'partial', 'final'])->default('advance')->after('payment_number');
            }

            if (! Schema::hasColumn('payments', 'method')) {
                $table->enum('method', ['cash', 'card', 'bank_transfer', 'other'])->default('cash')->after('payment_type');
            }

            if (! Schema::hasColumn('payments', 'amount')) {
                $table->decimal('amount', 12, 2)->default(0)->after('method');
            }

            if (! Schema::hasColumn('payments', 'notes')) {
                $table->text('notes')->nullable()->after('amount');
            }

            if (! Schema::hasColumn('payments', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            foreach (['repair_job_id', 'customer_id', 'created_by'] as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            foreach (['payment_number', 'payment_type', 'method', 'amount', 'notes'] as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
