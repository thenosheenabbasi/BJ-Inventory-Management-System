<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeStaff();

        $customers = Customer::query()
            ->with('createdBy')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('customer_code', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('customer_type'), function ($query) use ($request) {
                $query->where('customer_type', $request->string('customer_type')->toString());
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('customers.index', [
            'customers' => $customers,
            'customerTypes' => $this->customerTypes(),
            'statuses' => $this->statuses(),
            'canDelete' => $this->isAdmin(),
            'summary' => [
                'total' => Customer::count(),
                'repair' => Customer::whereIn('customer_type', ['repair_customer', 'both'])->count(),
                'purchase' => Customer::whereIn('customer_type', ['purchase_customer', 'both'])->count(),
                'active' => Customer::where('status', 'active')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorizeStaff();

        return view('customers.create', [
            'customer' => new Customer([
                'country' => 'UAE',
                'customer_type' => 'walk_in',
                'status' => 'active',
            ]),
            'customerTypes' => $this->customerTypes(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeStaff();

        $request->merge([
            'country' => $request->input('country') ?: 'UAE',
            'customer_type' => $request->input('customer_type') ?: 'walk_in',
            'status' => $request->input('status') ?: 'active',
        ]);

        $validated = $this->validatedCustomerData($request);
        $validated['customer_code'] = $this->generateCustomerCode();
        $validated['created_by'] = Auth::id();

        Customer::create($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer): View
    {
        $this->authorizeStaff();

        $customer->load(['user', 'createdBy']);
        $paymentHistory = Payment::query()
            ->whereNull('repair_job_id')
            ->where('customer_id', $customer->id)
            ->with(['allocations.invoice'])
            ->latest('payment_date')
            ->latest()
            ->limit(8)
            ->get();
        $invoiceHistory = Sale::query()
            ->where('customer_id', $customer->id)
            ->latest()
            ->limit(8)
            ->get();

        return view('customers.show', [
            'customer' => $customer,
            'canDelete' => $this->isAdmin(),
            'totalPaid' => Payment::whereNull('repair_job_id')
                ->where('customer_id', $customer->id)
                ->sum('total_payment_amount'),
            'outstandingBalance' => Sale::where('customer_id', $customer->id)->sum('remaining_amount'),
            'paymentHistory' => $paymentHistory,
            'invoiceHistory' => $invoiceHistory,
        ]);
    }

    public function edit(Customer $customer): View
    {
        $this->authorizeStaff();

        return view('customers.edit', [
            'customer' => $customer,
            'customerTypes' => $this->customerTypes(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorizeStaff();

        $validated = $this->validatedCustomerData($request, $customer);
        $validated['country'] = $validated['country'] ?: 'UAE';

        $customer->update($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        abort_unless($this->isAdmin(), 403);

        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    private function validatedCustomerData(Request $request, ?Customer $customer = null): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customer),
            ],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'customer_type' => ['required', Rule::in(array_keys($this->customerTypes()))],
            'status' => ['required', Rule::in(array_keys($this->statuses()))],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);
    }

    private function generateCustomerCode(): string
    {
        $lastNumber = Customer::query()
            ->where('customer_code', 'like', 'CU-%')
            ->pluck('customer_code')
            ->map(function (string $code): int {
                if (preg_match('/^CU-(\d+)$/', $code, $matches)) {
                    return (int) $matches[1];
                }

                return 1000;
            })
            ->max();

        $nextNumber = max(($lastNumber ?? 1000) + 1, 1001);

        do {
            $code = 'CU-'.$nextNumber;
            $nextNumber++;
        } while (Customer::where('customer_code', $code)->exists());

        return $code;
    }

    private function authorizeStaff(): void
    {
        abort_unless(in_array(Auth::user()?->role, [User::ROLE_ADMIN, User::ROLE_MANAGER], true), 403);
    }

    private function isAdmin(): bool
    {
        return Auth::user()?->role === User::ROLE_ADMIN;
    }

    private function customerTypes(): array
    {
        return [
            'walk_in' => 'Walk In',
            'repair_customer' => 'Repair Customer',
            'purchase_customer' => 'Purchase Customer',
            'both' => 'Repair & Purchase',
        ];
    }

    private function statuses(): array
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
        ];
    }
}
