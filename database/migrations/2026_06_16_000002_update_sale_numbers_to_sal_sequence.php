<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sales') || ! Schema::hasColumn('sales', 'sale_number')) {
            return;
        }

        $sales = DB::table('sales')->orderBy('id')->get(['id']);

        foreach ($sales as $sale) {
            DB::table('sales')
                ->where('id', $sale->id)
                ->update(['sale_number' => 'SALE-TEMP-'.$sale->id]);
        }

        $nextNumber = 1001;

        foreach ($sales as $sale) {
            DB::table('sales')
                ->where('id', $sale->id)
                ->update(['sale_number' => 'SAL-'.$nextNumber]);

            $nextNumber++;
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('sales') || ! Schema::hasColumn('sales', 'sale_number')) {
            return;
        }

        $year = now()->year;
        $sales = DB::table('sales')->orderBy('id')->get(['id']);
        $nextNumber = 1;

        foreach ($sales as $sale) {
            DB::table('sales')
                ->where('id', $sale->id)
                ->update(['sale_number' => sprintf('SAL-%d-%04d', $year, $nextNumber)]);

            $nextNumber++;
        }
    }
};
