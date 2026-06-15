<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('suppliers') || ! Schema::hasColumn('suppliers', 'supplier_code')) {
            return;
        }

        $nextNumber = 1001;

        DB::table('suppliers')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($supplier) use (&$nextNumber) {
                DB::table('suppliers')
                    ->where('id', $supplier->id)
                    ->update(['supplier_code' => 'SUP-'.$nextNumber]);

                $nextNumber++;
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('suppliers') || ! Schema::hasColumn('suppliers', 'supplier_code')) {
            return;
        }

        $year = now()->year;
        $nextNumber = 1;

        DB::table('suppliers')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($supplier) use (&$nextNumber, $year) {
                DB::table('suppliers')
                    ->where('id', $supplier->id)
                    ->update(['supplier_code' => sprintf('SUP-%d-%04d', $year, $nextNumber)]);

                $nextNumber++;
            });
    }
};
