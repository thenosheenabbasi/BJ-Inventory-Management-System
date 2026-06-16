<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sales')) {
            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'sale_number')) {
                $table->string('sale_number')->nullable()->unique();
            }

            if (! Schema::hasColumn('sales', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            }

            if (! Schema::hasColumn('sales', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0);
            }

            if (! Schema::hasColumn('sales', 'discount')) {
                $table->decimal('discount', 12, 2)->default(0);
            }

            if (! Schema::hasColumn('sales', 'vat')) {
                $table->decimal('vat', 12, 2)->default(0);
            }

            if (! Schema::hasColumn('sales', 'total_amount')) {
                $table->decimal('total_amount', 12, 2)->default(0);
            }

            if (! Schema::hasColumn('sales', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending');
            }

            if (! Schema::hasColumn('sales', 'notes')) {
                $table->text('notes')->nullable();
            }

            if (! Schema::hasColumn('sales', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sales')) {
            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            foreach (['customer_id', 'created_by'] as $column) {
                if (Schema::hasColumn('sales', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            foreach ([
                'sale_number',
                'subtotal',
                'discount',
                'vat',
                'total_amount',
                'payment_status',
                'notes',
            ] as $column) {
                if (Schema::hasColumn('sales', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
