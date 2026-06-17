<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\RepairJob;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizePaymentsAccess();
        $user = Auth::user();
        $customerId = $user?->role === User::ROLE_CUSTOMER
            ? Customer::where('user_id', $user->id)->value('id')
            : null;

        $paymentCustomersQuery = Customer::query()
            ->when($user?->role === User::ROLE_CUSTOMER, function ($query) use ($customerId) {
                $query->where('id', $customerId ?: 0);
            })
            ->whereHas('customerPayments')
            ->with([
                'payments' => function ($query) {
                    $query
                        ->whereNull('repair_job_id')
                        ->with(['createdBy', 'allocations.invoice', 'allocations.repairJob'])
                        ->latest('payment_date')
                        ->latest();
                },
                'sales',
                'repairJobs' => function ($query) {
                    $query->withSum('paymentAllocations as allocated_payment_amount', 'allocated_amount');
                },
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('customer_code', 'like', "%{$search}%")
                        ->orWhereHas('customerPayments', function ($paymentQuery) use ($search) {
                            $paymentQuery
                                ->where('payment_number', 'like', "%{$search}%")
                                ->orWhere('payment_method', 'like', "%{$search}%");
                        });
                });
            });

        $paymentCustomers = $paymentCustomersQuery
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('payments.index', [
            'paymentCustomers' => $paymentCustomers,
            'canCreate' => $this->canCreatePayments(),
            'summary' => [
                'total' => (clone $paymentCustomersQuery)->count(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizePaymentsCreate();

        $selectedCustomer = null;
        $invoices = collect();

        if ($request->filled('customer_id')) {
            $selectedCustomer = Customer::find($request->integer('customer_id'));

            if ($selectedCustomer) {
                $invoices = $this->pendingInvoicesForCustomer($selectedCustomer->id);
            }
        }

        return view('payments.create', [
            'customers' => Customer::where('status', 'active')->orderBy('full_name')->get(),
            'selectedCustomer' => $selectedCustomer,
            'invoices' => $invoices,
            'methods' => Payment::methods(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePaymentsCreate();

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'total_payment_amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'payment_method' => ['required', Rule::in(array_keys(Payment::methods()))],
            'payment_date' => ['required', 'date'],
            'allocation_mode' => ['required', Rule::in(['auto', 'manual'])],
            'notes' => ['nullable', 'string', 'max:3000'],
            'allocations' => ['nullable', 'array'],
            'allocations.*' => ['nullable', 'numeric', 'min:0'],
        ], [], [
            'customer_id' => 'customer',
            'total_payment_amount' => 'payment amount',
            'payment_method' => 'payment method',
            'payment_date' => 'payment date',
            'allocation_mode' => 'allocation mode',
        ]);

        DB::transaction(function () use ($validated) {
            $invoices = $this->pendingInvoicesForCustomer((int) $validated['customer_id'], true);

            if ($invoices->isEmpty()) {
                throw ValidationException::withMessages([
                    'customer_id' => 'This customer has no pending or partial invoices.',
                ]);
            }

            $amount = round((float) $validated['total_payment_amount'], 2);
            $allocations = $validated['allocation_mode'] === 'manual'
                ? $this->manualAllocations($validated['allocations'] ?? [], $invoices, $amount)
                : $this->autoAllocations($invoices, $amount);

            $payment = Payment::create([
                'customer_id' => $validated['customer_id'],
                'payment_number' => $this->generatePaymentNumber(),
                'payment_type' => 'partial',
                'method' => $validated['payment_method'],
                'amount' => $amount,
                'total_payment_amount' => $amount,
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($allocations as $invoiceKey => $allocatedAmount) {
                $invoice = $invoices->firstWhere('key', $invoiceKey);

                $payment->allocations()->create([
                    'invoice_type' => $invoice['type'],
                    'invoice_id' => $invoice['type'] === 'sale' ? $invoice['id'] : null,
                    'repair_job_id' => $invoice['type'] === 'repair' ? $invoice['id'] : null,
                    'allocated_amount' => $allocatedAmount,
                ]);

                if ($invoice['type'] === 'sale') {
                    $invoice['model']->syncPaymentState((float) $invoice['model']->received_amount + $allocatedAmount);
                }
            }

            return $payment;
        });

        return redirect()
            ->route('payments.index')
            ->with('success', 'Payment received successfully.');
    }

    public function show(Payment $payment): View
    {
        $this->authorizePaymentsAccess($payment);

        abort_if($payment->repair_job_id !== null, 404);

        $payment->load(['customer', 'createdBy', 'allocations.invoice', 'allocations.repairJob']);

        return view('payments.show', [
            'payment' => $payment,
            'canCreate' => $this->canCreatePayments(),
        ]);
    }

    public function slip(Customer $customer): View
    {
        $this->authorizeCustomerPaymentAccess($customer);

        $customer->load([
            'sales' => function ($query) {
                $query->with('items.battery')->orderBy('created_at')->orderBy('id');
            },
            'repairJobs' => function ($query) {
                $query
                    ->withSum('paymentAllocations as allocated_payment_amount', 'allocated_amount')
                    ->orderBy('created_at')
                    ->orderBy('id');
            },
            'payments' => function ($query) {
                $query
                    ->whereNull('repair_job_id')
                    ->with(['createdBy', 'allocations.invoice', 'allocations.repairJob'])
                    ->orderBy('payment_date')
                    ->orderBy('created_at')
                    ->orderBy('id');
            },
        ]);

        $saleInvoiceRows = $customer->sales
            ->toBase()
            ->map(function (Sale $sale): array {
                $itemLines = $sale->items
                    ->map(function ($item): array {
                        $batteryName = trim((string) ($item->battery?->brand.' '.$item->battery?->model));

                        return [
                            'details' => $batteryName !== '' ? $batteryName : 'Battery',
                            'quantity' => (int) $item->quantity,
                            'unit_price' => round((float) $item->unit_price, 2),
                        ];
                    })
                    ->values();

                return [
                    'number' => $sale->sale_number,
                    'date' => $sale->created_at,
                    'details' => $itemLines->isNotEmpty() ? $itemLines->pluck('details')->all() : ['Sale invoice'],
                    'quantities' => $itemLines->isNotEmpty() ? $itemLines->pluck('quantity')->all() : [0],
                    'unit_prices' => $itemLines->isNotEmpty() ? $itemLines->pluck('unit_price')->all() : [0],
                    'total' => round((float) $sale->total_amount, 2),
                    'received' => round((float) $sale->received_amount, 2),
                    'remaining' => round((float) $sale->remaining_amount, 2),
                ];
            });

        $repairInvoiceRows = $customer->repairJobs
            ->toBase()
            ->map(fn (RepairJob $repairJob): array => [
                'number' => $repairJob->repair_number,
                'date' => $repairJob->created_at,
                'details' => [$repairJob->battery_details ?: 'Repair invoice'],
                'quantities' => [(int) ($repairJob->quantity ?: 1)],
                'unit_prices' => [round((float) ($repairJob->unit_price ?: $repairJob->estimated_cost), 2)],
                'total' => round((float) $repairJob->estimated_cost, 2),
                'received' => round($repairJob->paidAmount(), 2),
                'remaining' => round($repairJob->remainingAmount(), 2),
            ]);

        $invoiceRows = $saleInvoiceRows
            ->merge($repairInvoiceRows)
            ->sortBy([
                ['date', 'asc'],
                ['number', 'asc'],
            ])
            ->values();

        $summary = [
            'invoice_count' => $invoiceRows->count(),
            'invoice_total' => round((float) $invoiceRows->sum('total'), 2),
            'received_total' => round((float) $invoiceRows->sum('received'), 2),
            'remaining_total' => round((float) $invoiceRows->sum('remaining'), 2),
            'last_payment' => $customer->latestPaymentRecord(),
        ];

        return view('payments.slip', [
            'customer' => $customer,
            'invoiceRows' => $invoiceRows,
            'payments' => $customer->paymentRecords()->sortBy([
                ['payment_date', 'asc'],
                ['created_at', 'asc'],
                ['id', 'asc'],
            ])->values(),
            'summary' => $summary,
        ]);
    }

    private function pendingInvoicesForCustomer(int $customerId, bool $lockForUpdate = false): Collection
    {
        $salesQuery = Sale::query()
            ->where('customer_id', $customerId)
            ->whereIn('payment_status', ['pending', 'partial'])
            ->where('remaining_amount', '>', 0)
            ->orderBy('created_at')
            ->orderBy('id');

        $repairsQuery = RepairJob::query()
            ->where('customer_id', $customerId)
            ->whereColumn('estimated_cost', '>', 'advance_payment')
            ->withSum('paymentAllocations as allocated_payment_amount', 'allocated_amount')
            ->orderBy('created_at')
            ->orderBy('id');

        if ($lockForUpdate) {
            $salesQuery->lockForUpdate();
            $repairsQuery->lockForUpdate();
        }

        $saleInvoices = $salesQuery->get()->map(function (Sale $sale): array {
            return [
                'key' => 'sale_'.$sale->id,
                'type' => 'sale',
                'type_label' => 'Sale',
                'id' => $sale->id,
                'number' => $sale->sale_number,
                'date' => $sale->created_at,
                'total_amount' => round((float) $sale->total_amount, 2),
                'received_amount' => round((float) $sale->received_amount, 2),
                'remaining_amount' => round((float) $sale->remaining_amount, 2),
                'status_label' => $sale->paymentStatusLabel(),
                'model' => $sale,
            ];
        });

        $repairInvoices = $repairsQuery->get()
            ->map(function (RepairJob $repairJob): array {
                return [
                    'key' => 'repair_'.$repairJob->id,
                    'type' => 'repair',
                    'type_label' => 'Repair',
                    'id' => $repairJob->id,
                    'number' => $repairJob->repair_number,
                    'date' => $repairJob->created_at,
                    'total_amount' => round((float) $repairJob->estimated_cost, 2),
                    'received_amount' => round($repairJob->paidAmount(), 2),
                    'remaining_amount' => round($repairJob->remainingAmount(), 2),
                    'status_label' => $repairJob->statusLabel(),
                    'model' => $repairJob,
                ];
            })
            ->filter(fn (array $invoice): bool => $invoice['remaining_amount'] > 0);

        return $saleInvoices
            ->merge($repairInvoices)
            ->sortBy([
                ['date', 'asc'],
                ['id', 'asc'],
            ])
            ->values();
    }

    private function autoAllocations(Collection $invoices, float $amount): array
    {
        $available = round((float) $invoices->sum('remaining_amount'), 2);

        if ($amount > $available) {
            throw ValidationException::withMessages([
                'total_payment_amount' => 'Payment amount cannot exceed the customer outstanding balance.',
            ]);
        }

        $remainingPayment = $amount;
        $allocations = [];

        foreach ($invoices as $invoice) {
            if ($remainingPayment <= 0) {
                break;
            }

            $invoiceRemaining = round((float) $invoice['remaining_amount'], 2);
            $allocated = round(min($invoiceRemaining, $remainingPayment), 2);

            if ($allocated > 0) {
                $allocations[$invoice['key']] = $allocated;
                $remainingPayment = round($remainingPayment - $allocated, 2);
            }
        }

        if (abs(round(array_sum($allocations), 2) - $amount) > 0.009) {
            throw ValidationException::withMessages([
                'total_payment_amount' => 'Payment amount must be fully allocated.',
            ]);
        }

        return $allocations;
    }

    private function manualAllocations(array $submittedAllocations, Collection $invoices, float $amount): array
    {
        $invoicesByKey = $invoices->keyBy('key');
        $allocations = [];

        foreach ($submittedAllocations as $invoiceKey => $allocationAmount) {
            $allocationAmount = round((float) $allocationAmount, 2);

            if ($allocationAmount <= 0) {
                continue;
            }

            $invoice = $invoicesByKey->get((string) $invoiceKey);

            if (! $invoice) {
                throw ValidationException::withMessages([
                    'allocations' => 'Selected invoice is not available for payment.',
                ]);
            }

            if ($allocationAmount > round((float) $invoice['remaining_amount'], 2)) {
                throw ValidationException::withMessages([
                    "allocations.{$invoiceKey}" => 'Allocation cannot exceed invoice remaining amount.',
                ]);
            }

            $allocations[$invoiceKey] = $allocationAmount;
        }

        if (abs(round(array_sum($allocations), 2) - $amount) > 0.009) {
            throw ValidationException::withMessages([
                'allocations' => 'Total allocation must equal payment amount.',
            ]);
        }

        return $allocations;
    }

    private function generatePaymentNumber(): string
    {
        $lastSequence = Payment::query()
            ->where('payment_number', 'like', 'PAY-%')
            ->pluck('payment_number')
            ->map(function (string $paymentNumber): int {
                if (preg_match('/^PAY-1-(\d+)$/', $paymentNumber, $matches)) {
                    return 1000 + (int) $matches[1];
                }

                if (preg_match('/^PAY-\d{4}-(\d+)$/', $paymentNumber, $matches)) {
                    return 1000 + (int) $matches[1];
                }

                if (preg_match('/^PAY-(\d+)$/', $paymentNumber, $matches)) {
                    return (int) $matches[1];
                }

                return 0;
            })
            ->max();

        $nextSequence = max(($lastSequence ?? 1000) + 1, 1001);

        do {
            $paymentNumber = 'PAY-'.$nextSequence;
            $nextSequence++;
        } while (Payment::where('payment_number', $paymentNumber)->exists());

        return $paymentNumber;
    }

    private function authorizePaymentsAccess(?Payment $payment = null): void
    {
        $user = Auth::user();

        if (in_array($user?->role, [User::ROLE_ADMIN, User::ROLE_MANAGER], true)) {
            return;
        }

        if ($user?->role === User::ROLE_CUSTOMER && $payment === null) {
            return;
        }

        abort_unless(
            $user?->role === User::ROLE_CUSTOMER
            && $payment?->customer?->user_id === $user->id,
            403
        );
    }

    private function authorizeCustomerPaymentAccess(Customer $customer): void
    {
        $user = Auth::user();

        if (in_array($user?->role, [User::ROLE_ADMIN, User::ROLE_MANAGER], true)) {
            return;
        }

        abort_unless(
            $user?->role === User::ROLE_CUSTOMER && $customer->user_id === $user->id,
            403
        );
    }

    private function authorizePaymentsCreate(): void
    {
        abort_unless($this->canCreatePayments(), 403);
    }

    private function canCreatePayments(): bool
    {
        return in_array(Auth::user()?->role, [User::ROLE_ADMIN, User::ROLE_MANAGER], true);
    }
}
