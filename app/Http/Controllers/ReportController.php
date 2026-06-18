<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\RepairJob;
use App\Models\Sale;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeReportsAccess();

        return view('reports.index', $this->reportData($request));
    }

    public function downloadPdf(Request $request): Response
    {
        $this->authorizeReportsAccess();

        $data = $this->reportData($request);
        $customerPart = $data['selectedCustomer']
            ? Str::slug($data['selectedCustomer']->full_name)
            : 'all-customers';
        $filename = sprintf(
            'payment-summary-report-%s-%s-to-%s.pdf',
            $customerPart,
            $data['startDate']->format('Y-m-d'),
            $data['endDate']->format('Y-m-d')
        );

        return Pdf::loadView('reports.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }

    private function reportData(Request $request): array
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
        ]);

        $startDate = Carbon::parse($validated['start_date'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $endDate = Carbon::parse($validated['end_date'] ?? now()->endOfMonth()->toDateString())->endOfDay();
        $customerId = isset($validated['customer_id']) ? (int) $validated['customer_id'] : null;

        if ($startDate->greaterThan($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $salesQuery = Sale::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($customerId, fn ($query) => $query->where('customer_id', $customerId));
        $repairsQuery = RepairJob::query()
            ->withSum('paymentAllocations as allocated_payment_amount', 'allocated_amount')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($customerId, fn ($query) => $query->where('customer_id', $customerId));
        $paymentsQuery = $this->paymentDateRangeQuery(Payment::query(), $startDate, $endDate)
            ->when($customerId, fn ($query) => $query->where('customer_id', $customerId));

        $payments = (clone $paymentsQuery)->get(['amount', 'total_payment_amount']);
        $repairs = (clone $repairsQuery)->get();

        $salesTotal = round((float) (clone $salesQuery)->sum('total_amount'), 2);
        $salesReceived = round((float) (clone $salesQuery)->sum('received_amount'), 2);
        $salesRemaining = round((float) (clone $salesQuery)->sum('remaining_amount'), 2);
        $repairTotal = round((float) $repairs->sum('estimated_cost'), 2);
        $repairPaid = round((float) $repairs->sum(fn (RepairJob $repairJob): float => $repairJob->paidAmount()), 2);
        $repairRemaining = round((float) $repairs->sum(fn (RepairJob $repairJob): float => $repairJob->remainingAmount()), 2);
        $collectionTotal = round($payments->sum(fn (Payment $payment): float => $payment->receivedAmount()), 2);

        $saleDetails = (clone $salesQuery)
            ->with(['customer', 'items.battery'])
            ->latest()
            ->get();

        $repairDetails = (clone $repairsQuery)
            ->with('customer')
            ->latest()
            ->get();

        $paymentDetails = (clone $paymentsQuery)
            ->with(['customer', 'allocations.invoice', 'allocations.repairJob'])
            ->latest('payment_date')
            ->latest()
            ->get();

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'customers' => Customer::query()->orderBy('full_name')->get(['id', 'customer_code', 'full_name', 'phone']),
            'selectedCustomer' => $customerId ? Customer::find($customerId) : null,
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
            ],
            'saleDetails' => $saleDetails,
            'repairDetails' => $repairDetails,
            'paymentDetails' => $paymentDetails,
            'generatedAt' => now(),
        ];
    }

    private function paymentDateRangeQuery(Builder $query, Carbon $startDate, Carbon $endDate): Builder
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
