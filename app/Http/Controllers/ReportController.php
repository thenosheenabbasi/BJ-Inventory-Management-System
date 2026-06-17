<?php

namespace App\Http\Controllers;

use App\Models\BatteryInventory;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\RepairJob;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeReportsAccess();

        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $startDate = Carbon::parse($validated['start_date'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $endDate = Carbon::parse($validated['end_date'] ?? now()->endOfMonth()->toDateString())->endOfDay();

        if ($startDate->greaterThan($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $salesQuery = Sale::query()->whereBetween('created_at', [$startDate, $endDate]);
        $repairsQuery = RepairJob::query()
            ->withSum('paymentAllocations as allocated_payment_amount', 'allocated_amount')
            ->whereBetween('created_at', [$startDate, $endDate]);
        $paymentsQuery = $this->paymentDateRangeQuery(Payment::query(), $startDate, $endDate);

        $payments = (clone $paymentsQuery)->get(['amount', 'total_payment_amount']);
        $repairs = (clone $repairsQuery)->get();

        $salesTotal = round((float) (clone $salesQuery)->sum('total_amount'), 2);
        $salesReceived = round((float) (clone $salesQuery)->sum('received_amount'), 2);
        $salesRemaining = round((float) (clone $salesQuery)->sum('remaining_amount'), 2);
        $repairTotal = round((float) $repairs->sum('estimated_cost'), 2);
        $repairPaid = round((float) $repairs->sum(fn (RepairJob $repairJob): float => $repairJob->paidAmount()), 2);
        $repairRemaining = round((float) $repairs->sum(fn (RepairJob $repairJob): float => $repairJob->remainingAmount()), 2);
        $collectionTotal = round($payments->sum(fn (Payment $payment): float => $payment->receivedAmount()), 2);

        $topSellingBatteries = SaleItem::query()
            ->select([
                'battery_inventory_id',
                DB::raw('SUM(quantity) as quantity_sold'),
                DB::raw('SUM(total_price) as sales_total'),
            ])
            ->whereHas('sale', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->with('battery')
            ->groupBy('battery_inventory_id')
            ->orderByDesc('quantity_sold')
            ->limit(8)
            ->get();

        $recentCollections = (clone $paymentsQuery)
            ->with('customer')
            ->latest('payment_date')
            ->latest()
            ->limit(10)
            ->get();

        $lowStockBatteries = BatteryInventory::query()
            ->whereColumn('stock_quantity', '<=', 'low_stock_alert_quantity')
            ->orderBy('stock_quantity')
            ->orderBy('brand')
            ->limit(8)
            ->get();

        $outstandingCustomers = Customer::query()
            ->with([
                'sales',
                'repairJobs' => function ($query) {
                    $query->withSum('paymentAllocations as allocated_payment_amount', 'allocated_amount');
                },
            ])
            ->get()
            ->map(fn (Customer $customer): array => [
                'customer' => $customer,
                'outstanding' => $customer->outstandingBalanceTotal(),
            ])
            ->filter(fn (array $row): bool => $row['outstanding'] > 0)
            ->sortByDesc('outstanding')
            ->take(8)
            ->values();

        return view('reports.index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => [
                'sales_count' => (clone $salesQuery)->count(),
                'sales_total' => $salesTotal,
                'sales_received' => $salesReceived,
                'sales_remaining' => $salesRemaining,
                'repair_count' => $repairs->count(),
                'repair_total' => $repairTotal,
                'repair_paid' => $repairPaid,
                'repair_remaining' => $repairRemaining,
                'collection_count' => $payments->count(),
                'collection_total' => $collectionTotal,
                'gross_business' => round($salesTotal + $repairTotal, 2),
                'gross_received' => round($salesReceived + $repairPaid, 2),
                'gross_remaining' => round($salesRemaining + $repairRemaining, 2),
                'inventory_stock' => BatteryInventory::sum('stock_quantity'),
                'inventory_value' => BatteryInventory::query()
                    ->selectRaw('COALESCE(SUM(stock_quantity * sale_price), 0) as total')
                    ->value('total'),
                'low_stock_count' => BatteryInventory::query()
                    ->whereColumn('stock_quantity', '<=', 'low_stock_alert_quantity')
                    ->count(),
            ],
            'topSellingBatteries' => $topSellingBatteries,
            'recentCollections' => $recentCollections,
            'lowStockBatteries' => $lowStockBatteries,
            'outstandingCustomers' => $outstandingCustomers,
        ]);
    }

    private function paymentDateRangeQuery($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->where(function ($dateQuery) use ($startDate, $endDate) {
            $dateQuery
                ->whereBetween('payment_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->orWhere(function ($fallbackQuery) use ($startDate, $endDate) {
                    $fallbackQuery
                        ->whereNull('payment_date')
                        ->whereBetween('created_at', [$startDate, $endDate]);
                });
        });
    }

    private function authorizeReportsAccess(): void
    {
        abort_unless(in_array(Auth::user()?->role, [User::ROLE_ADMIN, User::ROLE_MANAGER], true), 403);
    }
}
