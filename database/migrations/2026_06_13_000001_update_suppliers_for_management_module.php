<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('suppliers')) {
            return;
        }

        Schema::table('suppliers', function (Blueprint $table) {
            if (! Schema::hasColumn('suppliers', 'supplier_code')) {
                $table->string('supplier_code')->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('suppliers', 'company_name')) {
                $table->string('company_name')->nullable()->after('supplier_code');
            }

            if (! Schema::hasColumn('suppliers', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('company_name');
            }

            if (! Schema::hasColumn('suppliers', 'whatsapp')) {
                $table->string('whatsapp', 40)->nullable()->after('phone');
            }

            if (! Schema::hasColumn('suppliers', 'city')) {
                $table->string('city')->default('Dubai')->after('address');
            }

            if (! Schema::hasColumn('suppliers', 'country')) {
                $table->string('country')->default('UAE')->after('city');
            }

            if (! Schema::hasColumn('suppliers', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('country');
            }

            if (! Schema::hasColumn('suppliers', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            }
        });

        $year = now()->year;

        DB::table('suppliers')
            ->whereNull('company_name')
            ->when(Schema::hasColumn('suppliers', 'name'), function ($query) {
                $query->update(['company_name' => DB::raw('name')]);
            });

        DB::table('suppliers')
            ->whereNull('contact_person')
            ->update(['contact_person' => '']);

        DB::table('suppliers')
            ->whereNull('whatsapp')
            ->update(['whatsapp' => DB::raw("COALESCE(phone, '')")]);

        DB::table('suppliers')
            ->whereNull('city')
            ->update(['city' => 'Dubai']);

        DB::table('suppliers')
            ->whereNull('country')
            ->update(['country' => 'UAE']);

        DB::table('suppliers')
            ->whereNull('status')
            ->update(['status' => 'active']);

        DB::table('suppliers')
            ->whereNull('supplier_code')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($supplier, int $index) use ($year) {
                DB::table('suppliers')
                    ->where('id', $supplier->id)
                    ->update(['supplier_code' => sprintf('SUP-%d-%04d', $year, $index + 1)]);
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('suppliers')) {
            return;
        }

        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }

            foreach (['supplier_code', 'company_name', 'contact_person', 'whatsapp', 'city', 'country', 'status'] as $column) {
                if (Schema::hasColumn('suppliers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
