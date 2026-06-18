<?php

namespace App\Http\Controllers;

use App\Models\BatteryInventory;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SalesController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeSalesAccess();
        $user = Auth::user();

        $sales = Sale::query()
            ->with(['customer', 'items.battery', 'createdBy'])
            ->withCount('items')
            ->when($user?->role === User::ROLE_CUSTOMER, function ($query) use ($user) {
                $query->whereHas('customer', fn ($customerQuery) => $customerQuery->where('user_id', $user->id));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('sale_number', 'like', "%{$search}%")
                        ->orWhere('payment_status', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery
                                ->where('full_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('sales.index', [
            'sales' => $sales,
            'paymentStatuses' => Sale::paymentStatuses(),
            'canManage' => $this->canManageSales(),
            'canDelete' => $this->isAdmin(),
            'summary' => [
                'total' => $user?->role === User::ROLE_CUSTOMER
                    ? Sale::whereHas('customer', fn ($query) => $query->where('user_id', $user->id))->count()
                    : Sale::count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorizeSalesManagement();

        return view('sales.create', [
            'sale' => new Sale([
                'payment_status' => 'pending',
                'discount' => 0,
                'vat' => 0,
            ]),
            'customers' => Customer::where('status', 'active')->orderBy('full_name')->get(),
            'batteries' => $this->availableBatteries(),
            'paymentStatuses' => Sale::paymentStatuses(),
            'saleItems' => collect([new \App\Models\SaleItem([
                'quantity' => 1,
                'unit_price' => 0,
                'total_price' => 0,
            ])]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSalesManagement();

        $request->merge([
            'payment_status' => $request->input('payment_status') ?: 'pending',
            'discount' => $request->input('discount') ?: 0,
            'vat' => $request->input('vat') ?: 0,
        ]);

        $validated = $this->validatedSaleData($request);

        try {
            DB::transaction(function () use ($validated) {
                $batteryIds = collect($validated['items'])->pluck('battery_inventory_id')->unique()->values();
                $batteries = BatteryInventory::query()
                    ->whereIn('id', $batteryIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $itemRows = $this->buildItemRows($validated['items'], $batteries);
                $this->ensureStockAvailable($itemRows, $batteries);
                $totals = $this->calculateTotals($itemRows, $validated);

                $sale = Sale::create([
                    'sale_number' => $this->generateSaleNumber(),
                    'customer_id' => $validated['customer_id'],
                    'subtotal' => $totals['subtotal'],
                    'discount' => $totals['discount'],
                    'vat' => $totals['vat'],
                    'total_amount' => $totals['total_amount'],
                    'received_amount' => 0,
                    'remaining_amount' => $totals['total_amount'],
                    'payment_status' => 'pending',
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => Auth::id(),
                ]);

                $sale->items()->createMany($itemRows->map(fn (array $item): array => [
                    'battery_inventory_id' => $item['battery_inventory_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ])->all());

                $this->decreaseInventory($itemRows, $batteries);
            });
        } catch (ValidationException $exception) {
            throw $exception;
        }

        return redirect()
            ->route('sales.index')
            ->with('success', 'Sale battery created successfully.');
    }

    public function show(Sale $sale): View
    {
        $this->authorizeSalesAccess($sale);

        $sale->load(['customer', 'items.battery', 'createdBy']);

        return view('sales.show', [
            'sale' => $sale,
            'paymentStatuses' => Sale::paymentStatuses(),
            'canManage' => $this->canManageSales(),
            'canDelete' => $this->isAdmin(),
        ]);
    }

    public function slip(Sale $sale): View
    {
        $this->authorizeSalesAccess($sale);

        $sale->load(['customer', 'items.battery', 'createdBy']);

        return view('sales.slip', [
            'sale' => $sale,
        ]);
    }

    public function edit(Sale $sale): View
    {
        $this->authorizeSalesManagement();

        $sale->load(['items.battery', 'customer']);

        return view('sales.edit', [
            'sale' => $sale,
            'customers' => Customer::where('status', 'active')
                ->orWhere('id', $sale->customer_id)
                ->orderBy('full_name')
                ->get(),
            'batteries' => $this->availableBatteries($sale),
            'paymentStatuses' => Sale::paymentStatuses(),
            'saleItems' => $sale->items,
        ]);
    }

    public function update(Request $request, Sale $sale): RedirectResponse
    {
        $this->authorizeSalesManagement();

        $request->merge([
            'payment_status' => $request->input('payment_status') ?: 'pending',
            'discount' => $request->input('discount') ?: 0,
            'vat' => $request->input('vat') ?: 0,
        ]);

        $validated = $this->validatedSaleData($request);

        DB::transaction(function () use ($sale, $validated) {
            $sale->load('items');

            $oldRows = $sale->items->map(fn ($item): array => [
                'battery_inventory_id' => (int) $item->battery_inventory_id,
                'quantity' => (int) $item->quantity,
            ]);
            $newBatteryIds = collect($validated['items'])->pluck('battery_inventory_id');
            $batteryIds = $oldRows->pluck('battery_inventory_id')->merge($newBatteryIds)->unique()->values();

            $batteries = BatteryInventory::query()
                ->whereIn('id', $batteryIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $this->restoreInventory($oldRows, $batteries);

            $itemRows = $this->buildItemRows($validated['items'], $batteries);
            $this->ensureStockAvailable($itemRows, $batteries);
            $totals = $this->calculateTotals($itemRows, $validated);

            $sale->update([
                'customer_id' => $validated['customer_id'],
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'vat' => $totals['vat'],
                'total_amount' => $totals['total_amount'],
                'remaining_amount' => max(round($totals['total_amount'] - (float) $sale->received_amount, 2), 0),
                'notes' => $validated['notes'] ?? null,
            ]);

            $sale->syncPaymentState((float) $sale->received_amount);

            $sale->items()->delete();
            $sale->items()->createMany($itemRows->map(fn (array $item): array => [
                'battery_inventory_id' => $item['battery_inventory_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['total_price'],
            ])->all());

            $this->decreaseInventory($itemRows, $batteries);
        });

        return redirect()
            ->route('sales.index')
            ->with('success', 'Sale battery updated successfully.');
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        abort_unless($this->isAdmin(), 403);

        DB::transaction(function () use ($sale) {
            $sale->load('items');
            $batteryIds = $sale->items->pluck('battery_inventory_id')->unique()->values();
            $batteries = BatteryInventory::query()
                ->whereIn('id', $batteryIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $this->restoreInventory($sale->items->map(fn ($item): array => [
                'battery_inventory_id' => (int) $item->battery_inventory_id,
                'quantity' => (int) $item->quantity,
            ]), $batteries);

            $sale->delete();
        });

        return redirect()
            ->route('sales.index')
            ->with('success', 'Sale battery deleted successfully.');
    }

    private function validatedSaleData(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'payment_status' => ['required', Rule::in(array_keys(Sale::paymentStatuses()))],
            'discount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'vat' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.battery_inventory_id' => ['required', 'exists:battery_inventories,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
        ], [
            'items.required' => 'Please add at least one battery.',
            'items.*.battery_inventory_id.required' => 'Please select a battery.',
            'items.*.battery_inventory_id.exists' => 'Please select a valid battery.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
        ], [
            'customer_id' => 'customer',
            'payment_status' => 'payment status',
            'discount' => 'discount',
            'vat' => 'VAT',
            'items.*.battery_inventory_id' => 'battery',
            'items.*.quantity' => 'quantity',
        ]);
    }

    private function buildItemRows(array $items, Collection $batteries): Collection
    {
        return collect($items)
            ->map(function (array $item) use ($batteries): array {
                $battery = $batteries->get((int) $item['battery_inventory_id']);

                if (! $battery) {
                    throw ValidationException::withMessages([
                        'items' => 'Selected battery is no longer available.',
                    ]);
                }

                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $battery->sale_price;

                return [
                    'battery_inventory_id' => (int) $battery->id,
                    'quantity' => $quantity,
                    'unit_price' => round($unitPrice, 2),
                    'total_price' => round($quantity * $unitPrice, 2),
                ];
            })
            ->groupBy('battery_inventory_id')
            ->map(function (Collection $rows, int $batteryId) use ($batteries): array {
                $quantity = $rows->sum('quantity');
                $unitPrice = (float) $batteries->get($batteryId)->sale_price;

                return [
                    'battery_inventory_id' => $batteryId,
                    'quantity' => $quantity,
                    'unit_price' => round($unitPrice, 2),
                    'total_price' => round($quantity * $unitPrice, 2),
                ];
            })
            ->values();
    }

    private function ensureStockAvailable(Collection $itemRows, Collection $batteries): void
    {
        foreach ($itemRows as $item) {
            $battery = $batteries->get($item['battery_inventory_id']);

            if (! $battery || $item['quantity'] > (int) $battery->stock_quantity) {
                throw ValidationException::withMessages([
                    'items' => 'Insufficient stock.',
                ]);
            }
        }
    }

    private function calculateTotals(Collection $itemRows, array $data): array
    {
        $subtotal = round($itemRows->sum('total_price'), 2);
        $discount = round((float) ($data['discount'] ?? 0), 2);
        $vat = round((float) ($data['vat'] ?? 0), 2);

        if ($discount > $subtotal) {
            throw ValidationException::withMessages([
                'discount' => 'Discount cannot be greater than subtotal.',
            ]);
        }

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'vat' => $vat,
            'total_amount' => round($subtotal - $discount + $vat, 2),
        ];
    }

    private function decreaseInventory(Collection $itemRows, Collection $batteries): void
    {
        foreach ($itemRows as $item) {
            $battery = $batteries->get($item['battery_inventory_id']);
            $battery->decrement('stock_quantity', $item['quantity']);
        }
    }

    private function restoreInventory(Collection $itemRows, Collection $batteries): void
    {
        foreach ($itemRows as $item) {
            $battery = $batteries->get((int) $item['battery_inventory_id']);

            if ($battery) {
                $battery->increment('stock_quantity', (int) $item['quantity']);
            }
        }
    }

    private function generateSaleNumber(): string
    {
        $lastNumber = Sale::query()
            ->where('sale_number', 'like', 'SAL-%')
            ->pluck('sale_number')
            ->map(function (string $saleNumber): int {
                if (preg_match('/^SAL-(\d+)$/', $saleNumber, $matches)) {
                    return (int) $matches[1];
                }

                if (preg_match('/^SAL-\d{4}-(\d+)$/', $saleNumber, $matches)) {
                    return 1000 + (int) $matches[1];
                }

                return 1000;
            })
            ->max();

        $nextNumber = max(($lastNumber ?? 1000) + 1, 1001);

        do {
            $saleNumber = 'SAL-'.$nextNumber;
            $nextNumber++;
        } while (Sale::where('sale_number', $saleNumber)->exists());

        return $saleNumber;
    }

    private function availableBatteries(?Sale $sale = null): Collection
    {
        $saleBatteryIds = $sale
            ? $sale->items()->pluck('battery_inventory_id')->filter()->all()
            : [];

        return BatteryInventory::query()
            ->where(function ($query) use ($saleBatteryIds) {
                $query
                    ->where(function ($activeQuery) {
                        $activeQuery
                            ->where('status', 'active')
                            ->where('stock_quantity', '>', 0);
                    })
                    ->orWhereIn('id', $saleBatteryIds);
            })
            ->orderBy('brand')
            ->orderBy('model')
            ->get();
    }

    private function authorizeSalesAccess(?Sale $sale = null): void
    {
        if ($this->canManageSales()) {
            return;
        }

        $user = Auth::user();

        if ($user?->role === User::ROLE_CUSTOMER && $sale === null) {
            return;
        }

        abort_unless(
            $user?->role === User::ROLE_CUSTOMER
            && $sale?->customer?->user_id === $user->id,
            403
        );
    }

    private function authorizeSalesManagement(): void
    {
        abort_unless($this->canManageSales(), 403);
    }

    private function canManageSales(): bool
    {
        return in_array(Auth::user()?->role, [User::ROLE_ADMIN, User::ROLE_MANAGER], true);
    }

    private function isAdmin(): bool
    {
        return Auth::user()?->role === User::ROLE_ADMIN;
    }
}
