<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('customers', 'customer_code')) {
                $table->string('customer_code')->unique()->after('user_id');
            }

            if (! Schema::hasColumn('customers', 'full_name')) {
                $table->string('full_name')->after('customer_code');
            }

            if (! Schema::hasColumn('customers', 'phone')) {
                $table->string('phone')->after('full_name');
            }

            if (! Schema::hasColumn('customers', 'whatsapp')) {
                $table->string('whatsapp')->nullable()->after('phone');
            }

            if (! Schema::hasColumn('customers', 'email')) {
                $table->string('email')->nullable()->unique()->after('whatsapp');
            }

            if (! Schema::hasColumn('customers', 'city')) {
                $table->string('city')->nullable()->after('email');
            }

            if (! Schema::hasColumn('customers', 'country')) {
                $table->string('country')->default('UAE')->after('city');
            }

            if (! Schema::hasColumn('customers', 'customer_type')) {
                $table->enum('customer_type', ['walk_in', 'repair_customer', 'purchase_customer', 'both'])->default('walk_in')->after('country');
            }

            if (! Schema::hasColumn('customers', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('customer_type');
            }

            if (! Schema::hasColumn('customers', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }

            if (! Schema::hasColumn('customers', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $columns = [
                'user_id',
                'customer_code',
                'full_name',
                'phone',
                'whatsapp',
                'email',
                'city',
                'country',
                'customer_type',
                'status',
                'notes',
                'created_by',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('customers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
