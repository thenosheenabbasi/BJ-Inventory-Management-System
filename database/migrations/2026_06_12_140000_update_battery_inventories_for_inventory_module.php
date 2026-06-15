<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('battery_inventories')) {
            return;
        }

        Schema::table('battery_inventories', function (Blueprint $table) {
            if (! Schema::hasColumn('battery_inventories', 'battery_code')) {
                $table->string('battery_code')->unique()->after('id');
            }

            if (! Schema::hasColumn('battery_inventories', 'brand')) {
                $table->string('brand')->after('battery_code');
            }

            if (! Schema::hasColumn('battery_inventories', 'model')) {
                $table->string('model')->after('brand');
            }

            if (! Schema::hasColumn('battery_inventories', 'compatible_models')) {
                $table->text('compatible_models')->nullable()->after('model');
            }

            if (! Schema::hasColumn('battery_inventories', 'battery_type')) {
                $table->string('battery_type')->nullable()->after('compatible_models');
            }

            if (! Schema::hasColumn('battery_inventories', 'voltage')) {
                $table->string('voltage')->nullable()->after('battery_type');
            }

            if (! Schema::hasColumn('battery_inventories', 'capacity')) {
                $table->string('capacity')->nullable()->after('voltage');
            }

            if (! Schema::hasColumn('battery_inventories', 'cell_count')) {
                $table->unsignedSmallInteger('cell_count')->nullable()->after('capacity');
            }

            if (! Schema::hasColumn('battery_inventories', 'condition')) {
                $table->enum('condition', ['new', 'refurbished', 'used'])->default('new')->after('cell_count');
            }

            if (! Schema::hasColumn('battery_inventories', 'purchase_price')) {
                $table->decimal('purchase_price', 10, 2)->default(0)->after('condition');
            }

            if (! Schema::hasColumn('battery_inventories', 'sale_price')) {
                $table->decimal('sale_price', 10, 2)->default(0)->after('purchase_price');
            }

            if (! Schema::hasColumn('battery_inventories', 'stock_quantity')) {
                $table->unsignedInteger('stock_quantity')->default(0)->after('sale_price');
            }

            if (! Schema::hasColumn('battery_inventories', 'low_stock_alert_quantity')) {
                $table->unsignedInteger('low_stock_alert_quantity')->default(0)->after('stock_quantity');
            }

            if (! Schema::hasColumn('battery_inventories', 'warranty_days')) {
                $table->unsignedInteger('warranty_days')->default(0)->after('low_stock_alert_quantity');
            }

            if (! Schema::hasColumn('battery_inventories', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('warranty_days')->constrained('suppliers')->nullOnDelete();
            }

            if (! Schema::hasColumn('battery_inventories', 'notes')) {
                $table->text('notes')->nullable()->after('supplier_id');
            }

            if (! Schema::hasColumn('battery_inventories', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('notes');
            }

            if (! Schema::hasColumn('battery_inventories', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('battery_inventories')) {
            return;
        }

        Schema::table('battery_inventories', function (Blueprint $table) {
            foreach (['supplier_id', 'created_by'] as $column) {
                if (Schema::hasColumn('battery_inventories', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            $columns = [
                'battery_code',
                'brand',
                'model',
                'compatible_models',
                'battery_type',
                'voltage',
                'capacity',
                'cell_count',
                'condition',
                'purchase_price',
                'sale_price',
                'stock_quantity',
                'low_stock_alert_quantity',
                'warranty_days',
                'notes',
                'status',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('battery_inventories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
