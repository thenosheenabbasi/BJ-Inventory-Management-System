<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('repair_jobs')) {
            return;
        }

        Schema::table('repair_jobs', function (Blueprint $table) {
            if (! Schema::hasColumn('repair_jobs', 'quantity')) {
                $table->unsignedInteger('quantity')->default(1)->after('battery_details');
            }

            if (! Schema::hasColumn('repair_jobs', 'unit_price')) {
                $table->decimal('unit_price', 12, 2)->default(0)->after('quantity');
            }
        });

        DB::table('repair_jobs')
            ->where(function ($query) {
                $query->whereNull('quantity')->orWhere('quantity', 0);
            })
            ->update(['quantity' => 1]);

        DB::table('repair_jobs')
            ->where(function ($query) {
                $query->whereNull('unit_price')->orWhere('unit_price', 0);
            })
            ->update(['unit_price' => DB::raw('COALESCE(estimated_cost, 0)')]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('repair_jobs')) {
            return;
        }

        Schema::table('repair_jobs', function (Blueprint $table) {
            foreach (['quantity', 'unit_price'] as $column) {
                if (Schema::hasColumn('repair_jobs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
