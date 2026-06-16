<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) {
                if (! Schema::hasColumn('sales', 'received_amount')) {
                    $table->decimal('received_amount', 12, 2)->default(0)->after('total_amount');
                }

                if (! Schema::hasColumn('sales', 'remaining_amount')) {
                    $table->decimal('remaining_amount', 12, 2)->default(0)->after('received_amount');
                }
            });

            DB::table('sales')->orderBy('id')->chunkById(100, function ($sales): void {
                foreach ($sales as $sale) {
                    $total = (float) ($sale->total_amount ?? 0);
                    $received = $sale->payment_status === 'paid' ? $total : 0;

                    DB::table('sales')
                        ->where('id', $sale->id)
                        ->update([
                            'received_amount' => round($received, 2),
                            'remaining_amount' => round(max($total - $received, 0), 2),
                        ]);
                }
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (! Schema::hasColumn('payments', 'total_payment_amount')) {
                    $table->decimal('total_payment_amount', 12, 2)->default(0)->after('amount');
                }

                if (! Schema::hasColumn('payments', 'payment_method')) {
                    $table->string('payment_method')->nullable()->after('total_payment_amount');
                }

                if (! Schema::hasColumn('payments', 'payment_date')) {
                    $table->date('payment_date')->nullable()->after('payment_method');
                }
            });

            DB::table('payments')
                ->where('total_payment_amount', 0)
                ->update([
                    'total_payment_amount' => DB::raw('amount'),
                    'payment_method' => DB::raw('method'),
                    'payment_date' => DB::raw('DATE(created_at)'),
                ]);
        }

        if (! Schema::hasTable('payment_allocations')) {
            Schema::create('payment_allocations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
                $table->foreignId('invoice_id')->constrained('sales')->cascadeOnDelete();
                $table->decimal('allocated_amount', 12, 2);
                $table->timestamps();

                $table->index(['invoice_id', 'payment_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                foreach (['total_payment_amount', 'payment_method', 'payment_date'] as $column) {
                    if (Schema::hasColumn('payments', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) {
                foreach (['received_amount', 'remaining_amount'] as $column) {
                    if (Schema::hasColumn('sales', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
