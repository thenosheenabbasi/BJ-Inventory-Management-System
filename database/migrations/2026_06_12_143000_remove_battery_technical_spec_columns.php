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
            foreach ($this->columns() as $column) {
                if (Schema::hasColumn('battery_inventories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('battery_inventories')) {
            return;
        }

        Schema::table('battery_inventories', function (Blueprint $table) {
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
        });
    }

    private function columns(): array
    {
        return [
            'compatible_models',
            'battery_type',
            'voltage',
            'capacity',
            'cell_count',
        ];
    }
};
