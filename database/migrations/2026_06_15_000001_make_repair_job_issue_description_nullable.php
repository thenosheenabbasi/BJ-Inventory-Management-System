<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('repair_jobs') || ! Schema::hasColumn('repair_jobs', 'issue_description')) {
            return;
        }

        Schema::table('repair_jobs', function (Blueprint $table) {
            $table->text('issue_description')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('repair_jobs') || ! Schema::hasColumn('repair_jobs', 'issue_description')) {
            return;
        }

        DB::table('repair_jobs')
            ->whereNull('issue_description')
            ->update(['issue_description' => '']);

        Schema::table('repair_jobs', function (Blueprint $table) {
            $table->text('issue_description')->nullable(false)->change();
        });
    }
};
