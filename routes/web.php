<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\BatteryInventoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RepairJobController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SupplierController;
use App\Models\Customer;
use App\Models\BatteryInventory;
use App\Models\Payment;
use App\Models\RepairJob;
use App\Models\Sale;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $startOfMonth = now()->startOfMonth();
    $endOfMonth = now()->endOfMonth();
    $today = today();

    $recentRepairJobs = RepairJob::query()
        ->with('customer')
        ->latest()
        ->limit(5)
        ->get();

    $customersThisMonth = Customer::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
    $customersLastMonth = Customer::whereBetween('created_at', [
        now()->subMonthNoOverflow()->startOfMonth(),
        now()->subMonthNoOverflow()->endOfMonth(),
    ])->count();
    $customerGrowth = $customersLastMonth > 0
        ? round((($customersThisMonth - $customersLastMonth) / $customersLastMonth) * 100)
        : ($customersThisMonth > 0 ? 100 : 0);

    $repairJobsForBalance = RepairJob::query()
        ->withSum('paymentAllocations as allocated_payment_amount', 'allocated_amount')
        ->get(['id', 'estimated_cost', 'advance_payment']);

    $repairBalanceDue = $repairJobsForBalance->sum(function (RepairJob $repairJob): float {
        return $repairJob->remainingAmount();
    });

    $repairEstimatedTotal = RepairJob::sum('estimated_cost');
    $repairPaidTotal = Payment::whereNotNull('repair_job_id')->sum('amount');
    $salesRemainingTotal = (float) Sale::sum('remaining_amount');
    $monthlySales = Sale::query()
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
    $monthlyRepairJobs = RepairJob::query()
        ->withSum('paymentAllocations as allocated_payment_amount', 'allocated_amount')
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->get(['id', 'estimated_cost', 'advance_payment']);
    $monthlySaleTotal = (float) (clone $monthlySales)->sum('total_amount');
    $monthlySaleReceived = (float) (clone $monthlySales)->sum('received_amount');
    $monthlySalePending = (float) (clone $monthlySales)->sum('remaining_amount');
    $monthlyRepairTotal = (float) $monthlyRepairJobs->sum('estimated_cost');
    $monthlyRepairReceived = (float) $monthlyRepairJobs->sum(fn (RepairJob $repairJob): float => $repairJob->paidAmount());
    $monthlyRepairPending = (float) $monthlyRepairJobs->sum(fn (RepairJob $repairJob): float => $repairJob->remainingAmount());
    $monthlyBusinessTotal = round($monthlySaleTotal + $monthlyRepairTotal, 2);
    $monthlyEarningTotal = round($monthlySaleReceived + $monthlyRepairReceived, 2);
    $monthlyPendingTotal = round($monthlySalePending + $monthlyRepairPending, 2);
    $todaySaleTotal = (float) Sale::whereDate('created_at', $today)->sum('total_amount');
    $todaySaleReceived = (float) Sale::whereDate('created_at', $today)->sum('received_amount');
    $todayRepairJobs = RepairJob::query()
        ->withSum('paymentAllocations as allocated_payment_amount', 'allocated_amount')
        ->whereDate('created_at', $today)
        ->get(['id', 'estimated_cost', 'advance_payment']);
    $todayRepairTotal = (float) $todayRepairJobs->sum('estimated_cost');
    $todayRepairReceived = (float) $todayRepairJobs->sum(fn (RepairJob $repairJob): float => $repairJob->paidAmount());
    $batteryPurchaseValue = BatteryInventory::query()
        ->selectRaw('COALESCE(SUM(stock_quantity * purchase_price), 0) as total')
        ->value('total');
    $batterySaleValue = BatteryInventory::query()
        ->selectRaw('COALESCE(SUM(stock_quantity * sale_price), 0) as total')
        ->value('total');
    $pendingSaleInvoices = Sale::query()
        ->with('customer')
        ->where('remaining_amount', '>', 0)
        ->latest()
        ->limit(10)
        ->get()
        ->toBase()
        ->map(fn (Sale $sale): array => [
            'invoice' => $sale->sale_number,
            'date' => $sale->created_at,
            'customer' => $sale->customer?->full_name ?: '-',
            'phone' => $sale->customer?->phone ?: '-',
            'amount' => round((float) $sale->remaining_amount, 2),
        ]);
    $pendingRepairInvoices = RepairJob::query()
        ->with('customer')
        ->withSum('paymentAllocations as allocated_payment_amount', 'allocated_amount')
        ->latest()
        ->limit(10)
        ->get()
        ->toBase()
        ->map(fn (RepairJob $repairJob): array => [
            'invoice' => $repairJob->repair_number,
            'date' => $repairJob->created_at,
            'customer' => $repairJob->customer?->full_name ?: '-',
            'phone' => $repairJob->customer?->phone ?: '-',
            'amount' => round($repairJob->remainingAmount(), 2),
        ])
        ->filter(fn (array $invoice): bool => $invoice['amount'] > 0);
    $pendingClientInvoices = $pendingSaleInvoices
        ->merge($pendingRepairInvoices)
        ->sortByDesc(fn (array $invoice): int => $invoice['date']?->timestamp ?? 0)
        ->take(8)
        ->values();

    return view('admin.dashboard', [
        'dashboardStats' => [
            'totalCustomers' => Customer::count(),
            'activeCustomers' => Customer::where('status', 'active')->count(),
            'inactiveCustomers' => Customer::where('status', 'inactive')->count(),
            'customersThisMonth' => $customersThisMonth,
            'customerGrowth' => $customerGrowth,
            'totalBatteries' => BatteryInventory::count(),
            'activeBatteries' => BatteryInventory::where('status', 'active')->count(),
            'totalBatteryStock' => BatteryInventory::sum('stock_quantity'),
            'batteryStockValue' => $batterySaleValue,
            'batteryPurchaseValue' => $batteryPurchaseValue,
            'batteryPotentialProfit' => max((float) $batterySaleValue - (float) $batteryPurchaseValue, 0),
            'lowStockBatteries' => BatteryInventory::whereColumn('stock_quantity', '<=', 'low_stock_alert_quantity')->count(),
            'totalRepairJobs' => RepairJob::count(),
            'pendingRepairs' => RepairJob::where('status', '!=', 'delivered')->count(),
            'completedRepairs' => RepairJob::where('status', 'delivered')->count(),
            'receivedRepairs' => RepairJob::where('status', 'received')->count(),
            'repairingRepairs' => RepairJob::where('status', 'repairing')->count(),
            'readyRepairs' => RepairJob::where('status', 'ready_for_pickup')->count(),
            'repairEstimatedTotal' => $repairEstimatedTotal,
            'pendingRepairPayments' => $repairBalanceDue,
            'pendingSalePayments' => $salesRemainingTotal,
            'totalPendingPayments' => round($salesRemainingTotal + $repairBalanceDue, 2),
            'totalRepairAdvance' => $repairPaidTotal,
            'todayRepairAmount' => $todayRepairTotal,
            'todayRepairPaid' => $todayRepairReceived,
            'todayBusinessAmount' => round($todaySaleTotal + $todayRepairTotal, 2),
            'todayBusinessReceived' => round($todaySaleReceived + $todayRepairReceived, 2),
            'monthlySaleAmount' => $monthlySaleTotal,
            'monthlySaleReceived' => $monthlySaleReceived,
            'monthlySalePending' => $monthlySalePending,
            'monthlyRepairAmount' => $monthlyRepairTotal,
            'monthlyRepairPaid' => $monthlyRepairReceived,
            'monthlyRepairPending' => $monthlyRepairPending,
            'monthlyBusinessTotal' => $monthlyBusinessTotal,
            'monthlyEarningTotal' => $monthlyEarningTotal,
            'monthlyPendingTotal' => $monthlyPendingTotal,
        ],
        'pendingClientInvoices' => $pendingClientInvoices,
        'recentRepairJobs' => $recentRepairJobs,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('customers', CustomerController::class);
    Route::resource('battery-inventory', BatteryInventoryController::class)
        ->parameters(['battery-inventory' => 'batteryInventory']);
    Route::resource('suppliers', SupplierController::class)->except('show');
    Route::resource('repair-jobs', RepairJobController::class)
        ->parameters(['repair-jobs' => 'repairJob']);
    Route::get('repair-jobs/{repairJob}/slip', [RepairJobController::class, 'slip'])
        ->name('repair-jobs.slip');
    Route::get('sales/{sale}/slip', [SalesController::class, 'slip'])
        ->name('sales.slip');
    Route::resource('sales', SalesController::class);
    Route::get('payments/customers/{customer}/slip', [PaymentController::class, 'slip'])
        ->name('payments.slip');
    Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('reports', [ReportController::class, 'index'])
        ->name('reports.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
