<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('battery_inventories')) {
            return;
        }

        if (Schema::hasColumn('battery_inventories', 'sku') && DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `battery_inventories` MODIFY `sku` varchar(255) NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('battery_inventories')) {
            return;
        }

        if (Schema::hasColumn('battery_inventories', 'sku') && DB::connection()->getDriverName() === 'mysql') {
            DB::statement("UPDATE `battery_inventories` SET `sku` = `battery_code` WHERE `sku` IS NULL AND `battery_code` IS NOT NULL");
            DB::statement("UPDATE `battery_inventories` SET `sku` = CONCAT('LEGACY-', `id`) WHERE `sku` IS NULL");
            DB::statement('ALTER TABLE `battery_inventories` MODIFY `sku` varchar(255) NOT NULL');
        }
    }
};
