<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('repair_jobs') || ! Schema::hasColumn('repair_jobs', 'repair_number')) {
            return;
        }

        DB::table('repair_jobs')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($repairJob) {
                DB::table('repair_jobs')
                    ->where('id', $repairJob->id)
                    ->update(['repair_number' => 'RB-TEMP-'.$repairJob->id]);
            });

        $nextNumber = 1001;

        DB::table('repair_jobs')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($repairJob) use (&$nextNumber) {
                $code = 'RB-'.$nextNumber;

                DB::table('repair_jobs')
                    ->where('id', $repairJob->id)
                    ->update(['repair_number' => $code]);

                if (Schema::hasTable('qr_codes') && Schema::hasColumn('qr_codes', 'code')) {
                    DB::table('qr_codes')
                        ->where('repair_job_id', $repairJob->id)
                        ->update(['code' => $code]);
                }

                $nextNumber++;
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('repair_jobs') || ! Schema::hasColumn('repair_jobs', 'repair_number')) {
            return;
        }

        DB::table('repair_jobs')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($repairJob) {
                DB::table('repair_jobs')
                    ->where('id', $repairJob->id)
                    ->update(['repair_number' => 'RJ-TEMP-'.$repairJob->id]);
            });

        $year = now()->year;
        $nextNumber = 1;

        DB::table('repair_jobs')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($repairJob) use (&$nextNumber, $year) {
                $code = sprintf('RJ-%d-%04d', $year, $nextNumber);

                DB::table('repair_jobs')
                    ->where('id', $repairJob->id)
                    ->update(['repair_number' => $code]);

                if (Schema::hasTable('qr_codes') && Schema::hasColumn('qr_codes', 'code')) {
                    DB::table('qr_codes')
                        ->where('repair_job_id', $repairJob->id)
                        ->update(['code' => $code]);
                }

                $nextNumber++;
            });
    }
};
