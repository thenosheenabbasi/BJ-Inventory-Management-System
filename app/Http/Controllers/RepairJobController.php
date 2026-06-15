<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\QrCode;
use App\Models\RepairJob;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RepairJobController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $statuses = RepairJob::statuses();

        $repairJobs = RepairJob::query()
            ->with(['customer', 'createdBy', 'qrCode', 'payments.createdBy', 'timelines.changedBy'])
            ->when($this->isCustomer(), function ($query) use ($user) {
                $query->whereHas('customer', function ($customerQuery) use ($user) {
                    $customerQuery->where('user_id', $user?->id);
                });
            }, function ($query) use ($request) {
                $query
                    ->when($request->filled('search'), function ($searchQuery) use ($request) {
                        $search = $request->string('search')->toString();

                        $searchQuery->where(function ($innerQuery) use ($search) {
                            $innerQuery
                                ->where('repair_number', 'like', "%{$search}%")
                                ->orWhere('battery_details', 'like', "%{$search}%")
                                ->orWhereHas('customer', function ($customerQuery) use ($search) {
                                    $customerQuery
                                        ->where('full_name', 'like', "%{$search}%")
                                        ->orWhere('phone', 'like', "%{$search}%");
                                });
                        });
                    })
                    ->when($request->filled('status'), function ($statusQuery) use ($request) {
                        $statusQuery->where('status', $request->string('status')->toString());
                    });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('repair-jobs.index', [
            'repairJobs' => $repairJobs,
            'statuses' => $statuses,
            'canManage' => $this->canManageRepairs(),
            'canDelete' => $this->isAdmin(),
            'summary' => [
                'total' => $this->repairScope()->count(),
                'active' => $this->repairScope()->where('status', '!=', 'delivered')->count(),
                'ready' => $this->repairScope()->where('status', 'ready_for_pickup')->count(),
                'delivered' => $this->repairScope()->where('status', 'delivered')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorizeRepairManagement();

        return view('repair-jobs.create', [
            'repairJob' => new RepairJob([
                'status' => 'received',
                'quantity' => 1,
                'unit_price' => 0,
                'estimated_cost' => 0,
                'advance_payment' => 0,
            ]),
            'customers' => Customer::orderBy('full_name')->get(),
            'statuses' => RepairJob::statuses(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeRepairManagement();

        $request->merge([
            'status' => $request->input('status') ?: 'received',
            'quantity' => $request->input('quantity') ?: 1,
            'unit_price' => $request->input('unit_price') ?: 0,
            'advance_payment' => $request->input('advance_payment') ?: 0,
        ]);

        $validated = $this->validatedRepairJobData($request);
        unset($validated['status_note']);
        $validated['estimated_cost'] = $this->calculateEstimatedCost($validated);
        $this->ensureAdvanceDoesNotExceedTotal($validated);
        $validated['repair_number'] = $this->generateRepairNumber();
        $validated['created_by'] = Auth::id();

        $repairJob = RepairJob::create($validated);

        $this->recordTimeline($repairJob, null, $repairJob->status, 'Repair job created.');
        $this->syncAdvancePayment($repairJob);
        $this->generateQrCode($repairJob);

        return redirect()
            ->route('repair-jobs.index')
            ->with('success', 'Repair battery record created successfully.');
    }

    public function show(Request $request, RepairJob $repairJob): View
    {
        $this->authorizeRepairView($repairJob);

        $repairJob->load([
            'customer',
            'createdBy',
            'qrCode',
            'payments.createdBy',
            'timelines.changedBy',
        ]);

        $activeTab = $request->string('tab')->toString() ?: 'overview';
        $tabs = ['overview', 'timeline', 'payments', 'attachments', 'customer'];

        if (! in_array($activeTab, $tabs, true)) {
            $activeTab = 'overview';
        }

        return view('repair-jobs.show', [
            'repairJob' => $repairJob,
            'statuses' => RepairJob::statuses(),
            'activeTab' => $activeTab,
            'tabs' => $tabs,
            'canManage' => $this->canManageRepairs(),
            'canDelete' => $this->isAdmin(),
        ]);
    }

    public function slip(RepairJob $repairJob): View
    {
        $this->authorizeRepairView($repairJob);

        $repairJob->load([
            'customer',
            'createdBy',
            'qrCode',
            'payments.createdBy',
        ]);

        return view('repair-jobs.slip', [
            'repairJob' => $repairJob,
        ]);
    }

    public function edit(RepairJob $repairJob): View
    {
        $this->authorizeRepairManagement();

        $repairJob->load('customer');

        return view('repair-jobs.edit', [
            'repairJob' => $repairJob,
            'customers' => Customer::orderBy('full_name')->get(),
            'statuses' => RepairJob::statuses(),
        ]);
    }

    public function update(Request $request, RepairJob $repairJob): RedirectResponse
    {
        $this->authorizeRepairManagement();

        $request->merge([
            'quantity' => $request->input('quantity') ?: 1,
            'unit_price' => $request->input('unit_price') ?: 0,
            'advance_payment' => $request->input('advance_payment') ?: 0,
        ]);

        $oldStatus = $repairJob->status;
        $validated = $this->validatedRepairJobData($request, $repairJob);
        unset($validated['status_note']);
        $validated['estimated_cost'] = $this->calculateEstimatedCost($validated);
        $this->ensureAdvanceDoesNotExceedTotal($validated);

        $repairJob->update($validated);

        if ($oldStatus !== $repairJob->status) {
            $this->recordTimeline(
                $repairJob,
                $oldStatus,
                $repairJob->status,
                $request->input('status_note') ?: 'Status updated.'
            );
        }

        $this->syncAdvancePayment($repairJob);
        $this->generateQrCode($repairJob);

        return redirect()
            ->route('repair-jobs.index')
            ->with('success', 'Repair battery record updated successfully.');
    }

    public function destroy(RepairJob $repairJob): RedirectResponse
    {
        abort_unless($this->isAdmin(), 403);

        $repairJob->delete();

        return redirect()
            ->route('repair-jobs.index')
            ->with('success', 'Repair battery record deleted successfully.');
    }

    private function validatedRepairJobData(Request $request, ?RepairJob $repairJob = null): array
    {
        return $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'battery_details' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'issue_description' => ['nullable', 'string', 'max:3000'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'advance_payment' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'expected_delivery_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(array_keys(RepairJob::statuses()))],
            'status_note' => ['nullable', 'string', 'max:1000'],
        ], [
            'required' => 'Please enter :attribute.',
            'customer_id.exists' => 'Please select a valid customer.',
            'numeric' => ':attribute must be a valid amount.',
            'status.in' => 'Please select a valid repair status.',
        ], [
            'customer_id' => 'customer',
            'battery_details' => 'battery model / name',
            'quantity' => 'quantity',
            'unit_price' => 'unit price',
            'issue_description' => 'issue description',
            'estimated_cost' => 'amount',
            'advance_payment' => 'advance payment',
            'expected_delivery_date' => 'expected delivery date',
            'status' => 'status',
            'status_note' => 'status note',
        ]);
    }

    private function generateRepairNumber(): string
    {
        $lastNumber = RepairJob::query()
            ->where('repair_number', 'like', 'RB-%')
            ->pluck('repair_number')
            ->map(function (string $repairNumber): int {
                if (preg_match('/^RB-(\d+)$/', $repairNumber, $matches)) {
                    return (int) $matches[1];
                }

                return 1000;
            })
            ->max();

        $nextNumber = max(($lastNumber ?? 1000) + 1, 1001);

        do {
            $repairNumber = 'RB-'.$nextNumber;
            $nextNumber++;
        } while (RepairJob::where('repair_number', $repairNumber)->exists());

        return $repairNumber;
    }

    private function calculateEstimatedCost(array $data): float
    {
        return round(((int) ($data['quantity'] ?? 1)) * ((float) ($data['unit_price'] ?? 0)), 2);
    }

    private function ensureAdvanceDoesNotExceedTotal(array $data): void
    {
        if ((float) ($data['advance_payment'] ?? 0) <= (float) ($data['estimated_cost'] ?? 0)) {
            return;
        }

        throw ValidationException::withMessages([
            'advance_payment' => 'Advance payment cannot be greater than amount.',
        ]);
    }

    private function generatePaymentNumber(): string
    {
        $lastNumber = Payment::query()
            ->where('payment_number', 'like', 'PAY-%')
            ->pluck('payment_number')
            ->map(function (string $paymentNumber): int {
                if (preg_match('/^PAY-(\d+)$/', $paymentNumber, $matches)) {
                    return (int) $matches[1];
                }

                return 1000;
            })
            ->max();

        $nextNumber = max(($lastNumber ?? 1000) + 1, 1001);

        do {
            $paymentNumber = 'PAY-'.$nextNumber;
            $nextNumber++;
        } while (Payment::where('payment_number', $paymentNumber)->exists());

        return $paymentNumber;
    }

    private function recordTimeline(RepairJob $repairJob, ?string $fromStatus, string $toStatus, ?string $notes = null): void
    {
        $repairJob->timelines()->create([
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'notes' => $notes,
            'changed_by' => Auth::id(),
        ]);
    }

    private function syncAdvancePayment(RepairJob $repairJob): void
    {
        $advanceAmount = (float) $repairJob->advance_payment;
        $advancePayment = $repairJob->payments()->where('payment_type', 'advance')->first();

        if ($advanceAmount <= 0) {
            $advancePayment?->delete();
            return;
        }

        $repairJob->payments()->updateOrCreate(
            ['payment_type' => 'advance'],
            [
                'customer_id' => $repairJob->customer_id,
                'payment_number' => $advancePayment?->payment_number ?: $this->generatePaymentNumber(),
                'method' => 'cash',
                'amount' => $advanceAmount,
                'notes' => 'Advance payment recorded with repair battery.',
                'created_by' => Auth::id(),
            ]
        );
    }

    private function generateQrCode(RepairJob $repairJob): void
    {
        $repairJob->qrCode()->updateOrCreate(
            ['repair_job_id' => $repairJob->id],
            [
                'code' => $repairJob->repair_number,
                'payload' => route('repair-jobs.show', $repairJob),
                'format' => 'svg',
                'generated_at' => now(),
            ]
        );
    }

    private function repairScope()
    {
        $query = RepairJob::query();

        if ($this->isCustomer()) {
            $query->whereHas('customer', function ($customerQuery) {
                $customerQuery->where('user_id', Auth::id());
            });
        }

        return $query;
    }

    private function authorizeRepairManagement(): void
    {
        abort_unless($this->canManageRepairs(), 403);
    }

    private function authorizeRepairView(RepairJob $repairJob): void
    {
        if ($this->canManageRepairs()) {
            return;
        }

        abort_unless(
            $this->isCustomer() && $repairJob->customer()->where('user_id', Auth::id())->exists(),
            403
        );
    }

    private function canManageRepairs(): bool
    {
        return in_array(Auth::user()?->role, [User::ROLE_ADMIN, User::ROLE_MANAGER], true);
    }

    private function isAdmin(): bool
    {
        return Auth::user()?->role === User::ROLE_ADMIN;
    }

    private function isCustomer(): bool
    {
        return Auth::user()?->role === User::ROLE_CUSTOMER;
    }
}
