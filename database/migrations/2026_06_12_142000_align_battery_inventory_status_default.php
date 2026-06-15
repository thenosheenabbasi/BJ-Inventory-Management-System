<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('battery_inventories') || ! Schema::hasColumn('battery_inventories', 'status')) {
            return;
        }

        DB::table('battery_inventories')
            ->whereNotIn('status', ['active', 'inactive'])
            ->update(['status' => 'active']);

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `battery_inventories` MODIFY `status` varchar(255) NOT NULL DEFAULT 'active'");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('battery_inventories') || ! Schema::hasColumn('battery_inventories', 'status')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `battery_inventories` MODIFY `status` varchar(255) NOT NULL DEFAULT 'available'");
        }
    }
};
