<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customers') || ! Schema::hasColumn('customers', 'name')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `customers` MODIFY `name` varchar(255) NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('customers') || ! Schema::hasColumn('customers', 'name')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("UPDATE `customers` SET `name` = `full_name` WHERE `name` IS NULL AND `full_name` IS NOT NULL");
            DB::statement("UPDATE `customers` SET `name` = '' WHERE `name` IS NULL");
            DB::statement('ALTER TABLE `customers` MODIFY `name` varchar(255) NOT NULL');
        }
    }
};
