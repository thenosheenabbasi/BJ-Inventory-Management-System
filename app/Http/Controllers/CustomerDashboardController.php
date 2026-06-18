<?php

namespace App\Http\Controllers;

use App\Models\RepairJob;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    public function index(): View
    {
        abort_unless(Auth::user()?->role === User::ROLE_CUSTOMER, 403);

        $customer = Auth::user()->customer;

        if (! $customer) {
            return view('customer.dashboard', [
                'customer' => null,
                'summary' => $this->emptySummary(),
                'pendingInvoices' => collect(),
                'recentInvoices' => collect(),
            ]);
        }

        $sales = Sale::query()
            ->where('customer_id', $customer->id)
            ->with('items')
            ->latest()
            ->get();
        $repairs = RepairJob::query()
            ->where('customer_id', $customer->id)
            ->withSum('paymentAllocations as allocated_payment_amount', 'allocated_amount')
            ->latest()
            ->get();

        $saleTotal = round((float) $sales->sum('total_amount'), 2);
        $salePaid = round((float) $sales->sum('received_amount'), 2);
        $salePending = round((float) $sales->sum('remaining_amount'), 2);
        $repairTotal = round((float) $repairs->sum('estimated_cost'), 2);
        $repairPaid = round((float) $repairs->sum(fn (RepairJob $repair): float => $repair->paidAmount()), 2);
        $repairPending = round((float) $repairs->sum(fn (RepairJob $repair): float => $repair->remainingAmount()), 2);

        $saleInvoices = $sales->toBase()->map(fn (Sale $sale): array => [
            'type' => 'Sale',
            'number' => $sale->sale_number,
            'date' => $sale->created_at,
            'quantity' => (int) $sale->items->sum('quantity'),
            'unit_price' => $sale->items->count() === 1
                ? (float) $sale->items->first()->unit_price
                : null,
            'total' => (float) $sale->total_amount,
            'paid' => (float) $sale->received_amount,
            'pending' => (float) $sale->remaining_amount,
            'status' => $sale->paymentStatusLabel(),
            'status_class' => $sale->paymentStatusBadgeClass(),
            'url' => route('sales.show', $sale),
        ]);
        $repairInvoices = $repairs->toBase()->map(function (RepairJob $repair): array {
            $paid = $repair->paidAmount();
            $pending = $repair->remainingAmount();
            $paymentStatus = $pending <= 0.00001
                ? 'Paid'
                : ($paid > 0 ? 'Partial' : 'Pending');

            return [
                'type' => 'Repair',
                'number' => $repair->repair_number,
                'date' => $repair->created_at,
                'quantity' => (int) ($repair->quantity ?: 1),
                'unit_price' => (float) $repair->unit_price,
                'total' => (float) $repair->estimated_cost,
                'paid' => $paid,
                'pending' => $pending,
                'status' => $paymentStatus,
                'status_class' => match ($paymentStatus) {
                    'Paid' => 'success',
                    'Partial' => 'warning',
                    default => 'secondary',
                },
                'url' => route('repair-jobs.show', $repair),
            ];
        });
        $invoices = $saleInvoices
            ->merge($repairInvoices)
            ->sortByDesc(fn (array $invoice): int => $invoice['date']?->timestamp ?? 0)
            ->values();

        return view('customer.dashboard', [
            'customer' => $customer,
            'summary' => [
                'total' => round($saleTotal + $repairTotal, 2),
                'paid' => round($salePaid + $repairPaid, 2),
                'pending' => round($salePending + $repairPending, 2),
                'invoice_count' => $invoices->count(),
                'pending_count' => $invoices->where('pending', '>', 0)->count(),
                'sale_total' => $saleTotal,
                'repair_total' => $repairTotal,
            ],
            'pendingInvoices' => $invoices->where('pending', '>', 0)->values(),
            'recentInvoices' => $invoices->take(8),
        ]);
    }

    private function emptySummary(): array
    {
        return [
            'total' => 0,
            'paid' => 0,
            'pending' => 0,
            'invoice_count' => 0,
            'pending_count' => 0,
            'sale_total' => 0,
            'repair_total' => 0,
        ];
    }
}
