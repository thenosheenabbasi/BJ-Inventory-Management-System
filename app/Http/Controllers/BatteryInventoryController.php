<?php

namespace App\Http\Controllers;

use App\Models\BatteryInventory;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BatteryInventoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeStaff();

        $batteries = BatteryInventory::query()
            ->with(['supplier', 'createdBy'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('battery_code', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->string('stock')->toString() === 'low', function ($query) {
                $query->whereColumn('stock_quantity', '<=', 'low_stock_alert_quantity');
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('battery-inventory.index', [
            'batteries' => $batteries,
            'conditions' => $this->conditions(),
            'statuses' => $this->statuses(),
            'canDelete' => $this->isAdmin(),
            'summary' => [
                'total' => BatteryInventory::count(),
                'active' => BatteryInventory::where('status', 'active')->count(),
                'lowStock' => BatteryInventory::whereColumn('stock_quantity', '<=', 'low_stock_alert_quantity')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorizeStaff();

        return view('battery-inventory.create', [
            'battery' => new BatteryInventory([
                'condition' => 'new',
                'status' => 'active',
                'stock_quantity' => 0,
                'low_stock_alert_quantity' => 0,
                'warranty_days' => 0,
            ]),
            'conditions' => $this->conditions(),
            'statuses' => $this->statuses(),
            'suppliers' => Supplier::orderBy('company_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeStaff();

        $request->merge([
            'condition' => $request->input('condition') ?: 'new',
            'status' => $request->input('status') ?: 'active',
        ]);

        $validated = $this->validatedBatteryData($request);
        $validated['battery_code'] = $this->generateBatteryCode();
        $validated['created_by'] = Auth::id();

        BatteryInventory::create($validated);

        return redirect()
            ->route('battery-inventory.index')
            ->with('success', 'Battery added successfully.');
    }

    public function show(BatteryInventory $batteryInventory): View
    {
        $this->authorizeStaff();

        $batteryInventory->load(['supplier', 'createdBy']);

        return view('battery-inventory.show', [
            'battery' => $batteryInventory,
            'conditions' => $this->conditions(),
            'statuses' => $this->statuses(),
            'canDelete' => $this->isAdmin(),
        ]);
    }

    public function edit(BatteryInventory $batteryInventory): View
    {
        $this->authorizeStaff();

        return view('battery-inventory.edit', [
            'battery' => $batteryInventory,
            'conditions' => $this->conditions(),
            'statuses' => $this->statuses(),
            'suppliers' => Supplier::orderBy('company_name')->get(),
        ]);
    }

    public function update(Request $request, BatteryInventory $batteryInventory): RedirectResponse
    {
        $this->authorizeStaff();

        $batteryInventory->update($this->validatedBatteryData($request, $batteryInventory));

        return redirect()
            ->route('battery-inventory.index')
            ->with('success', 'Battery updated successfully.');
    }

    public function destroy(BatteryInventory $batteryInventory): RedirectResponse
    {
        abort_unless($this->isAdmin(), 403);

        $batteryInventory->delete();

        return redirect()
            ->route('battery-inventory.index')
            ->with('success', 'Battery deleted successfully.');
    }

    private function validatedBatteryData(Request $request, ?BatteryInventory $battery = null): array
    {
        return $request->validate([
            'brand' => ['required', 'string', 'max:120'],
            'model' => ['required', 'string', 'max:160'],
            'condition' => ['required', Rule::in(array_keys($this->conditions()))],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'sale_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_alert_quantity' => ['required', 'integer', 'min:0'],
            'warranty_days' => ['required', 'integer', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'status' => ['required', Rule::in(array_keys($this->statuses()))],
        ], [
            'required' => 'Please enter :attribute.',
            'numeric' => ':attribute must be a valid amount.',
            'integer' => ':attribute must be a whole number.',
            'min' => ':attribute cannot be less than :min.',
            'max' => ':attribute is too long.',
            'condition.in' => 'Please select a valid condition.',
            'status.in' => 'Please select a valid status.',
            'supplier_id.exists' => 'Please select a valid supplier.',
        ], [
            'brand' => 'brand',
            'model' => 'model',
            'condition' => 'condition',
            'purchase_price' => 'purchase price',
            'sale_price' => 'sale price',
            'stock_quantity' => 'stock quantity',
            'low_stock_alert_quantity' => 'low stock alert quantity',
            'warranty_days' => 'warranty days',
            'supplier_id' => 'supplier',
            'notes' => 'notes',
            'status' => 'status',
        ]);
    }

    private function generateBatteryCode(): string
    {
        $lastNumber = BatteryInventory::query()
            ->where('battery_code', 'like', 'BAT-%')
            ->pluck('battery_code')
            ->map(function (string $code): int {
                if (preg_match('/^BAT-(\d+)$/', $code, $matches)) {
                    return (int) $matches[1];
                }

                return 1000;
            })
            ->max();

        $nextNumber = max(($lastNumber ?? 1000) + 1, 1001);

        do {
            $code = 'BAT-'.$nextNumber;
            $nextNumber++;
        } while (BatteryInventory::where('battery_code', $code)->exists());

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

    private function conditions(): array
    {
        return [
            'new' => 'New',
            'refurbished' => 'Refurbished',
            'used' => 'Used',
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
