<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeStaff();

        $suppliers = Supplier::query()
            ->with('createdBy')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('company_name', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('supplier_code', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('suppliers.index', [
            'suppliers' => $suppliers,
            'statuses' => $this->statuses(),
            'canDelete' => $this->isAdmin(),
            'summary' => [
                'total' => Supplier::count(),
                'active' => Supplier::where('status', 'active')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorizeStaff();

        return view('suppliers.create', [
            'supplier' => new Supplier([
                'city' => 'Dubai',
                'country' => 'UAE',
                'status' => 'active',
            ]),
            'statuses' => $this->statuses(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeStaff();

        $request->merge([
            'city' => $request->input('city') ?: 'Dubai',
            'country' => $request->input('country') ?: 'UAE',
            'status' => $request->input('status') ?: 'active',
        ]);

        $validated = $this->validatedSupplierData($request);
        $validated['supplier_code'] = $this->generateSupplierCode();
        $validated['created_by'] = Auth::id();

        Supplier::create($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function edit(Supplier $supplier): View
    {
        $this->authorizeStaff();

        return view('suppliers.edit', [
            'supplier' => $supplier,
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $this->authorizeStaff();

        $request->merge([
            'city' => $request->input('city') ?: 'Dubai',
            'country' => $request->input('country') ?: 'UAE',
            'status' => $request->input('status') ?: 'active',
        ]);

        $validated = $this->validatedSupplierData($request, $supplier);

        $supplier->update($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        abort_unless($this->isAdmin(), 403);

        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    private function validatedSupplierData(Request $request, ?Supplier $supplier = null): array
    {
        return $request->validate([
            'company_name' => ['required', 'string', 'max:180'],
            'contact_person' => ['required', 'string', 'max:160'],
            'phone' => ['required', 'string', 'max:40'],
            'whatsapp' => ['required', 'string', 'max:40'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('suppliers', 'email')->ignore($supplier),
            ],
            'address' => ['nullable', 'string', 'max:3000'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in(array_keys($this->statuses()))],
            'notes' => ['nullable', 'string', 'max:3000'],
        ], [
            'required' => 'Please enter :attribute.',
            'email' => 'Please enter a valid email address.',
            'max' => ':attribute is too long.',
            'status.in' => 'Please select a valid status.',
        ], [
            'company_name' => 'company name',
            'contact_person' => 'contact person',
            'phone' => 'phone',
            'whatsapp' => 'WhatsApp',
            'email' => 'email',
            'address' => 'address',
            'city' => 'city',
            'country' => 'country',
            'status' => 'status',
            'notes' => 'notes',
        ]);
    }

    private function generateSupplierCode(): string
    {
        $lastNumber = Supplier::query()
            ->where('supplier_code', 'like', 'SUP-%')
            ->pluck('supplier_code')
            ->map(function (string $code): int {
                if (preg_match('/^SUP-(\d+)$/', $code, $matches)) {
                    return (int) $matches[1];
                }

                return 1000;
            })
            ->max();

        $nextNumber = max(($lastNumber ?? 1000) + 1, 1001);

        do {
            $code = 'SUP-'.$nextNumber;
            $nextNumber++;
        } while (Supplier::where('supplier_code', $code)->exists());

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

    private function statuses(): array
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
        ];
    }
}
