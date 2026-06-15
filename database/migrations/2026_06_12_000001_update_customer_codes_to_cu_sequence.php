<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('customers') || ! Schema::hasColumn('customers', 'customer_code')) {
            return;
        }

        $customers = DB::table('customers')
            ->select('id')
            ->orderBy('id')
            ->get();

        foreach ($customers as $customer) {
            DB::table('customers')
                ->where('id', $customer->id)
                ->update(['customer_code' => 'TMP-CU-'.$customer->id]);
        }

        $sequence = 1001;

        foreach ($customers as $customer) {
            DB::table('customers')
                ->where('id', $customer->id)
                ->update(['customer_code' => 'CU-'.$sequence]);

            $sequence++;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
