<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('repair_jobs')) {
            return;
        }

        Schema::table('repair_jobs', function (Blueprint $table) {
            if (! Schema::hasColumn('repair_jobs', 'repair_number')) {
                $table->string('repair_number')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('repair_jobs', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('repair_number')->constrained('customers')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('repair_jobs', 'battery_details')) {
                $table->text('battery_details')->nullable()->after('customer_id');
            }

            if (! Schema::hasColumn('repair_jobs', 'issue_description')) {
                $table->text('issue_description')->nullable()->after('battery_details');
            }

            if (! Schema::hasColumn('repair_jobs', 'technician_notes')) {
                $table->text('technician_notes')->nullable()->after('issue_description');
            }

            if (! Schema::hasColumn('repair_jobs', 'estimated_cost')) {
                $table->decimal('estimated_cost', 12, 2)->default(0)->after('technician_notes');
            }

            if (! Schema::hasColumn('repair_jobs', 'advance_payment')) {
                $table->decimal('advance_payment', 12, 2)->default(0)->after('estimated_cost');
            }

            if (! Schema::hasColumn('repair_jobs', 'expected_delivery_date')) {
                $table->date('expected_delivery_date')->nullable()->after('advance_payment');
            }

            if (! Schema::hasColumn('repair_jobs', 'status')) {
                $table->enum('status', [
                    'received',
                    'diagnosis',
                    'waiting_approval',
                    'repairing',
                    'ready_for_pickup',
                    'delivered',
                ])->default('received')->after('expected_delivery_date');
            }

            if (! Schema::hasColumn('repair_jobs', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('repair_jobs')) {
            return;
        }

        Schema::table('repair_jobs', function (Blueprint $table) {
            foreach (['customer_id', 'created_by'] as $column) {
                if (Schema::hasColumn('repair_jobs', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            foreach ([
                'repair_number',
                'battery_details',
                'issue_description',
                'technician_notes',
                'estimated_cost',
                'advance_payment',
                'expected_delivery_date',
                'status',
            ] as $column) {
                if (Schema::hasColumn('repair_jobs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
