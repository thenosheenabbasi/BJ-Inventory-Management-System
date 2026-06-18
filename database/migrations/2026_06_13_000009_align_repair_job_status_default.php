<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('repair_jobs') || ! Schema::hasColumn('repair_jobs', 'status')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE repair_jobs MODIFY status VARCHAR(255) NOT NULL DEFAULT 'received'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('repair_jobs') || ! Schema::hasColumn('repair_jobs', 'status')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE repair_jobs MODIFY status VARCHAR(255) NOT NULL DEFAULT 'pending'");
    }
};
