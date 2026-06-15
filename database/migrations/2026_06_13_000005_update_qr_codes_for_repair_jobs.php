<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('qr_codes')) {
            return;
        }

        Schema::table('qr_codes', function (Blueprint $table) {
            if (! Schema::hasColumn('qr_codes', 'repair_job_id')) {
                $table->foreignId('repair_job_id')->nullable()->after('id')->constrained('repair_jobs')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('qr_codes', 'code')) {
                $table->string('code')->nullable()->unique()->after('repair_job_id');
            }

            if (! Schema::hasColumn('qr_codes', 'payload')) {
                $table->text('payload')->nullable()->after('code');
            }

            if (! Schema::hasColumn('qr_codes', 'format')) {
                $table->string('format')->default('svg')->after('payload');
            }

            if (! Schema::hasColumn('qr_codes', 'generated_at')) {
                $table->timestamp('generated_at')->nullable()->after('format');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('qr_codes')) {
            return;
        }

        Schema::table('qr_codes', function (Blueprint $table) {
            if (Schema::hasColumn('qr_codes', 'repair_job_id')) {
                $table->dropConstrainedForeignId('repair_job_id');
            }

            foreach (['code', 'payload', 'format', 'generated_at'] as $column) {
                if (Schema::hasColumn('qr_codes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
