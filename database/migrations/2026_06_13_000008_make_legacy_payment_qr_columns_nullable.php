<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'sale_id')) {
            DB::statement('ALTER TABLE payments MODIFY sale_id BIGINT UNSIGNED NULL');
        }

        if (Schema::hasTable('qr_codes')) {
            if (Schema::hasColumn('qr_codes', 'model_type')) {
                DB::statement('ALTER TABLE qr_codes MODIFY model_type VARCHAR(255) NULL');
            }

            if (Schema::hasColumn('qr_codes', 'model_id')) {
                DB::statement('ALTER TABLE qr_codes MODIFY model_id BIGINT UNSIGNED NULL');
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'sale_id')) {
            DB::statement('ALTER TABLE payments MODIFY sale_id BIGINT UNSIGNED NOT NULL');
        }

        if (Schema::hasTable('qr_codes')) {
            if (Schema::hasColumn('qr_codes', 'model_type')) {
                DB::statement('ALTER TABLE qr_codes MODIFY model_type VARCHAR(255) NOT NULL');
            }

            if (Schema::hasColumn('qr_codes', 'model_id')) {
                DB::statement('ALTER TABLE qr_codes MODIFY model_id BIGINT UNSIGNED NOT NULL');
            }
        }
    }
};
