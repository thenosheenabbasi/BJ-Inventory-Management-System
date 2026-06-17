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
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $startOfMonth = now()->startOfMonth();
    $endOfMonth = now()->endOfMonth();
    $today = today();

    $lowStockBatteries = BatteryInventory::query()
        ->whereColumn('stock_quantity', '<=', 'low_stock_alert_quantity')
        ->latest()
        ->limit(5)
        ->get();

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
        ->get(['estimated_cost', 'advance_payment']);

    $repairBalanceDue = $repairJobsForBalance->sum(function (RepairJob $repairJob): float {
        return $repairJob->remainingAmount();
    });

    $repairEstimatedTotal = RepairJob::sum('estimated_cost');
    $repairPaidTotal = Payment::whereNotNull('repair_job_id')->sum('amount');
    $batteryPurchaseValue = BatteryInventory::query()
        ->selectRaw('COALESCE(SUM(stock_quantity * purchase_price), 0) as total')
        ->value('total');
    $batterySaleValue = BatteryInventory::query()
        ->selectRaw('COALESCE(SUM(stock_quantity * sale_price), 0) as total')
        ->value('total');

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
            'totalRepairAdvance' => $repairPaidTotal,
            'todayRepairAmount' => RepairJob::whereDate('created_at', $today)->sum('estimated_cost'),
            'todayRepairPaid' => Payment::whereNotNull('repair_job_id')->whereDate('created_at', $today)->sum('amount'),
            'monthlyRepairAmount' => RepairJob::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('estimated_cost'),
            'monthlyRepairPaid' => Payment::whereNotNull('repair_job_id')->whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('amount'),
        ],
        'lowStockBatteries' => $lowStockBatteries,
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
