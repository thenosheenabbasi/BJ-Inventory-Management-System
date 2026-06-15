<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('suppliers') || ! Schema::hasColumn('suppliers', 'name')) {
            return;
        }

        DB::statement('ALTER TABLE suppliers MODIFY name VARCHAR(255) NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('suppliers') || ! Schema::hasColumn('suppliers', 'name')) {
            return;
        }

        DB::table('suppliers')
            ->whereNull('name')
            ->update(['name' => DB::raw("COALESCE(company_name, '')")]);

        DB::statement('ALTER TABLE suppliers MODIFY name VARCHAR(255) NOT NULL');
    }
};
